import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/app_config.dart';
import '../errors/api_call.dart';
import '../errors/app_exception.dart';
import 'api_endpoint_discovery.dart';

/// HTTP client for the team's Laravel REST API.
///
/// Every call targets `/api/v1/mobile/...` and carries the Sanctum bearer
/// token issued by `POST /auth/login`. There is no direct database access and
/// no third-party backend SDK.
class MobileApiClient {
  MobileApiClient({
    http.Client? httpClient,
    String? baseUrl,
    ApiEndpointDiscovery? endpointDiscovery,
  }) : _http = httpClient ?? http.Client(),
       _configuredBaseUrl = _normalizeBaseUrl(
         baseUrl ??
             (AppConfig.usesAutomaticDiscovery ? null : AppConfig.apiBaseUrl),
       ),
       _endpointDiscovery = endpointDiscovery ?? ApiEndpointDiscovery();

  final http.Client _http;
  final String? _configuredBaseUrl;
  final ApiEndpointDiscovery _endpointDiscovery;

  String? _token;

  /// Called when the API rejects a stored token so the app can sign out.
  void Function()? onUnauthenticated;

  void setToken(String? token) => _token = token;

  String? get token => _token;

  static String? _normalizeBaseUrl(String? baseUrl) {
    final normalized = baseUrl?.trim();
    if (normalized == null || normalized.isEmpty) return null;
    return normalized.replaceAll(RegExp(r'/+$'), '');
  }

  Uri _uri(String baseUrl, String path, [Map<String, String>? query]) {
    final normalizedPath = path.startsWith('/') ? path.substring(1) : path;
    final normalizedPrefix = AppConfig.apiPrefix.replaceAll(
      RegExp(r'^/+|/+$'),
      '',
    );
    final uri = Uri.parse('$baseUrl/$normalizedPrefix/$normalizedPath');

    if (query == null || query.isEmpty) return uri;

    return uri.replace(queryParameters: {...uri.queryParameters, ...query});
  }

  Map<String, String> _headers({bool authenticate = true}) {
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (authenticate && _token != null && _token!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $_token';
    }

    return headers;
  }

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, String>? query,
    bool authenticate = true,
  }) => _send(
    (baseUrl) => _http.get(
      _uri(baseUrl, path, query),
      headers: _headers(authenticate: authenticate),
    ),
  );

  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
    bool authenticate = true,
  }) => _send(
    (baseUrl) => _http.post(
      _uri(baseUrl, path),
      headers: _headers(authenticate: authenticate),
      body: jsonEncode(body ?? const <String, dynamic>{}),
    ),
  );

  Future<Map<String, dynamic>> patch(
    String path, {
    Map<String, dynamic>? body,
    bool authenticate = true,
  }) => _send(
    (baseUrl) => _http.patch(
      _uri(baseUrl, path),
      headers: _headers(authenticate: authenticate),
      body: jsonEncode(body ?? const <String, dynamic>{}),
    ),
  );

  Future<Map<String, dynamic>> put(
    String path, {
    Map<String, dynamic>? body,
    bool authenticate = true,
  }) => _send(
    (baseUrl) => _http.put(
      _uri(baseUrl, path),
      headers: _headers(authenticate: authenticate),
      body: jsonEncode(body ?? const <String, dynamic>{}),
    ),
  );

  Future<String> _resolveBaseUrl() async {
    final configuredBaseUrl = _configuredBaseUrl;
    if (configuredBaseUrl != null) return configuredBaseUrl;

    try {
      return await _endpointDiscovery.resolve();
    } on ApiEndpointDiscoveryException catch (error) {
      throw AppException(error.message, isNetworkError: true);
    }
  }

  Future<Map<String, dynamic>> _send(
    Future<http.Response> Function(String baseUrl) request,
  ) {
    return apiCall(() async {
      final baseUrl = await _resolveBaseUrl();
      final response = await request(
        baseUrl,
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 401) {
        onUnauthenticated?.call();
      }

      return decodeJsonResponse(response);
    });
  }

  void close() => _http.close();
}

/// Polls an endpoint on an interval so screens can refresh in place.
///
/// Laravel has no websocket transport, so periodic refresh is used instead of
/// a push-based subscription. The returned object cancels the
/// timer when its owner is disposed.
class ApiPoller {
  ApiPoller.periodic({
    required Future<void> Function() onTick,
    Duration interval = const Duration(seconds: 20),
  }) {
    _timer = Timer.periodic(interval, (_) => unawaited(onTick()));
  }

  Timer? _timer;

  void cancel() {
    _timer?.cancel();
    _timer = null;
  }
}

/// Thrown by repositories when the API returns an unexpected payload shape.
AppException invalidResponse() =>
    const AppException('The server returned an unexpected response.');
