import 'package:flutter/foundation.dart';

/// Application-wide configuration.
///
/// Supabase credentials are injected at build time with `SUPABASE_URL` and
/// `SUPABASE_ANON_KEY` Dart defines.
class AppConfig {
  const AppConfig._();

  static const String supabaseUrl = String.fromEnvironment('SUPABASE_URL');
  static const String supabaseAnonKey = String.fromEnvironment(
    'SUPABASE_ANON_KEY',
  );

  static bool get isHttps =>
      supabaseUrl.isNotEmpty && Uri.parse(supabaseUrl).scheme == 'https';

  static void validate() {
    if (supabaseUrl.isEmpty || supabaseAnonKey.isEmpty) {
      throw StateError(
        'SUPABASE_URL and SUPABASE_ANON_KEY must be set with --dart-define.',
      );
    }
    if (kReleaseMode && !isHttps) {
      throw StateError('SUPABASE_URL must use HTTPS in release builds.');
    }
  }
}
