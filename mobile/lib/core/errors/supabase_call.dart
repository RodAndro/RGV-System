import 'dart:async';
import 'dart:io';

import 'package:http/http.dart' as http;
import 'package:supabase_flutter/supabase_flutter.dart';

import 'app_exception.dart';

Future<T> supabaseCall<T>(Future<T> Function() action) async {
  try {
    return await action();
  } on AuthException catch (error) {
    if (error.message.contains('SocketException') ||
        error.message.contains('ClientException')) {
      throw const AppException(
        'Could not reach Supabase. Check your internet connection and try again.',
        isNetworkError: true,
      );
    }
    final statusCode = int.tryParse(error.statusCode ?? '') ?? 401;
    throw AppException(error.message, statusCode: statusCode);
  } on PostgrestException catch (error) {
    if (error.code == 'PGRST202' &&
        error.message.contains('mobile_employee_profile')) {
      throw const AppException(
        'Employee sign-in is not fully configured in Supabase. An administrator '
        'must apply database/supabase/mobile_backend.sql to the same Supabase '
        'project and reload its API schema.',
        statusCode: 503,
      );
    }
    final statusCode = switch (error.code) {
      '28000' => 401,
      '42501' => 403,
      '23505' => 409,
      '22023' || '23514' || 'P0001' => 422,
      _ => int.tryParse(error.code ?? ''),
    };
    throw AppException(error.message, statusCode: statusCode);
  } on SocketException {
    throw const AppException(
      'Could not reach Supabase. Check your internet connection and try again.',
      isNetworkError: true,
    );
  } on TimeoutException {
    throw const AppException(
      'The connection to Supabase timed out. Please try again.',
      isNetworkError: true,
    );
  } on http.ClientException {
    throw const AppException(
      'Could not reach Supabase. Check your internet connection and try again.',
      isNetworkError: true,
    );
  }
}
