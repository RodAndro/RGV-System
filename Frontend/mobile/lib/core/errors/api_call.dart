import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import 'app_exception.dart';

/// Runs an HTTP call and converts transport and API failures into
/// [AppException] so screens keep one error contract.
///
/// Laravel answers errors in one of two shapes:
///   {"message": "...", "errors": {"field": ["..."]}}   (validation)
///   {"error": {"code": "...", "message": "...", "details": [...]}}
/// Both are understood here.
Future<T> apiCall<T>(Future<T> Function() action) async {
  try {
    return await action();
  } on TimeoutException {
    throw const AppException(
      'The server took too long to respond. Please try again.',
      isNetworkError: true,
    );
  } on SocketException {
    throw const AppException(
      'Could not reach the server. Check your internet connection and that '
      'the API address is correct.',
      isNetworkError: true,
    );
  } on http.ClientException {
    throw const AppException(
      'Could not reach the server. Check your internet connection.',
      isNetworkError: true,
    );
  }
}

/// Maps a raw HTTP response onto parsed JSON, raising a detailed
/// [AppException] for any non-success status.
Map<String, dynamic> decodeJsonResponse(http.Response response) {
  final status = response.statusCode;
  Map<String, dynamic> body = const {};

  if (response.body.isNotEmpty) {
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is Map<String, dynamic>) {
        body = decoded;
      } else if (decoded is List) {
        body = {'data': decoded};
      }
    } on FormatException {
      if (status >= 200 && status < 300) {
        throw const AppException(
          'The server returned an unreadable response.',
          statusCode: 500,
        );
      }
    }
  }

  if (status >= 200 && status < 300) {
    return body;
  }

  throw _errorFromResponse(status, body);
}

AppException _errorFromResponse(int status, Map<String, dynamic> body) {
  final fieldErrors = <String, List<String>>{};
  final rawErrors = body['errors'];

  if (rawErrors is Map) {
    rawErrors.forEach((key, value) {
      if (value is List) {
        fieldErrors[key.toString()] = value.map((e) => e.toString()).toList();
      } else if (value != null) {
        fieldErrors[key.toString()] = [value.toString()];
      }
    });
  }

  String? message;

  final envelope = body['error'];
  if (envelope is Map) {
    message = envelope['message']?.toString();

    final details = envelope['details'];
    if (details is List) {
      for (final detail in details) {
        if (detail is Map) {
          final field = detail['field']?.toString();
          final detailMessage = detail['message']?.toString();
          if (field != null && detailMessage != null) {
            fieldErrors.putIfAbsent(field, () => []).add(detailMessage);
          }
        }
      }
    }
  }

  message ??= body['message']?.toString();

  if (message == null || message.isEmpty) {
    message = switch (status) {
      401 => 'Your session has expired. Please sign in again.',
      403 => 'You do not have permission to perform this action.',
      404 => 'That item could not be found.',
      409 => 'This action conflicts with the current state. Please refresh.',
      422 => 'Some fields are invalid.',
      429 => 'Too many requests. Please wait a moment and try again.',
      >= 500 => 'The server ran into a problem. Please try again shortly.',
      _ => 'The request could not be completed.',
    };
  }

  return AppException(message, statusCode: status, fieldErrors: fieldErrors);
}
