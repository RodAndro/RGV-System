import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../errors/app_exception.dart';

typedef TokenProvider = String? Function();

/// Thin JSON HTTP client for the Laravel mobile API.
///
/// Injects the bearer token on every request, parses Laravel's consistent
/// `{message, errors}` error shape, and normalises transport failures into
/// [AppException]s so the UI can show actionable messages.
class ApiClient {
  ApiClient({
    required this.baseUrl,
    TokenProvider? tokenProvider,
    http.Client? httpClient,
  })  : tokenProvider = tokenProvider ?? _noToken,
        _http = httpClient ?? http.Client();

  static String? _noToken() => null;

  final String baseUrl;

  /// Mutable so the composition root can wire it to the auth provider after
  /// both objects are constructed (the provider itself depends on this client
  /// through the repository, so the binding is completed one step later).
  TokenProvider tokenProvider;

  final http.Client _http;

  static const Duration _timeout = Duration(seconds: 30);

  Map<String, String> get _headers {
    final token = tokenProvider();
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  Uri _uri(String path, [Map<String, String>? query]) {
    final normalized = path.startsWith('/') ? path.substring(1) : path;
    return Uri.parse('$baseUrl/$normalized').replace(queryParameters: query);
  }

  Future<Map<String, dynamic>> get(String path, {Map<String, String>? query}) async {
    return _send(() => _http.get(_uri(path, query), headers: _headers));
  }

  Future<Map<String, dynamic>> post(String path, {Object? body}) async {
    return _send(() => _http.post(
          _uri(path),
          headers: _headers,
          body: body == null ? null : jsonEncode(body),
        ));
  }

  Future<Map<String, dynamic>> patch(String path, {Object? body}) async {
    return _send(() => _http.patch(
          _uri(path),
          headers: _headers,
          body: body == null ? null : jsonEncode(body),
        ));
  }

  Future<Map<String, dynamic>> put(String path, {Object? body}) async {
    return _send(() => _http.put(
          _uri(path),
          headers: _headers,
          body: body == null ? null : jsonEncode(body),
        ));
  }

  /// Multipart upload (used for return-proof photos). [files] maps a form field
  /// name to the local file path; [fields] carries the remaining form data
  /// (including nested array keys such as `items[0][borrow_item_id]`).
  Future<Map<String, dynamic>> postMultipart(
    String path, {
    required Map<String, String> fields,
    required Map<String, String> files,
  }) async {
    return _send(() async {
      final request = http.MultipartRequest('POST', _uri(path));

      final token = tokenProvider();
      request.headers['Accept'] = 'application/json';
      if (token != null) request.headers['Authorization'] = 'Bearer $token';

      fields.forEach((key, value) => request.fields[key] = value);
      for (final entry in files.entries) {
        request.files.add(await http.MultipartFile.fromPath(entry.key, entry.value));
      }

      final streamed = await request.send().timeout(_timeout);
      return http.Response.fromStream(streamed);
    });
  }

  Future<Map<String, dynamic>> _send(
    Future<http.Response> Function() request,
  ) async {
    http.Response response;
    try {
      response = await request().timeout(_timeout);
    } on TimeoutException {
      throw const AppException(
        'The server took too long to respond. Please try again.',
        isNetworkError: true,
      );
    } on SocketException {
      throw const AppException(
        'Cannot reach the server. Check your connection and try again.',
        isNetworkError: true,
      );
    } on http.ClientException {
      throw const AppException(
        'Cannot reach the server. Check your connection and try again.',
        isNetworkError: true,
      );
    }

    return _decode(response);
  }

  Map<String, dynamic> _decode(http.Response response) {
    Map<String, dynamic>? json;
    if (response.body.isNotEmpty) {
      try {
        final decoded = jsonDecode(utf8.decode(response.bodyBytes));
        if (decoded is Map<String, dynamic>) json = decoded;
      } on FormatException {
        json = null;
      }
    }

    final status = response.statusCode;
    if (status >= 200 && status < 300) {
      return json ?? const {};
    }

    final message = json?['message'] as String? ?? _defaultMessage(status);
    final errors = <String, List<String>>{};
    final rawErrors = json?['errors'];
    if (rawErrors is Map<String, dynamic>) {
      rawErrors.forEach((key, value) {
        if (value is List) {
          errors[key] = value.map((e) => e.toString()).toList();
        } else if (value is String) {
          errors[key] = [value];
        }
      });
    }

    throw AppException(message, statusCode: status, fieldErrors: errors);
  }

  String _defaultMessage(int status) {
    switch (status) {
      case 401:
        return 'Your session has expired. Please sign in again.';
      case 403:
        return 'You are not allowed to perform this action.';
      case 404:
        return 'The requested resource was not found.';
      case 409:
        return 'This request could not be completed.';
      case 422:
        return 'Please check your input and try again.';
      case 429:
        return 'Too many requests. Please try again shortly.';
      case 502:
      case 503:
      case 504:
        return 'The server is temporarily unavailable. Please try again.';
      default:
        return 'Something went wrong. Please try again.';
    }
  }
}
