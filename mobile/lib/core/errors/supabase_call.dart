import 'dart:async';
import 'dart:io';

import 'package:supabase_flutter/supabase_flutter.dart';

import 'app_exception.dart';

Future<T> supabaseCall<T>(Future<T> Function() action) async {
  try {
    return await action();
  } on AuthException catch (error) {
    throw AppException(error.message);
  } on PostgrestException catch (error) {
    throw AppException(error.message);
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
  }
}
