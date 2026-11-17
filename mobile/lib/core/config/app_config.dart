import 'package:flutter/foundation.dart';

/// Supabase configuration for local development and native builds.
///
/// When the app is launched with `--dart-define`, those values override the
/// defaults below. This keeps local development and debug builds working without
/// the native splash screen throwing before the app can start.
class AppConfig {
  const AppConfig._();

  static const String _defaultSupabaseUrl =
      'https://fywtxrptaqcmkzsgfehp.supabase.co';
  static const String _defaultSupabaseAnonKey =
      'sb_publishable_l0nUDT07PxzQ-nOnEK4E5w_fCfIlgM2';

  static const String supabaseUrl = String.fromEnvironment(
    'SUPABASE_URL',
    defaultValue: _defaultSupabaseUrl,
  );
  static const String supabaseAnonKey = String.fromEnvironment(
    'SUPABASE_ANON_KEY',
    defaultValue: _defaultSupabaseAnonKey,
  );

  static void validate() {
    final uri = Uri.tryParse(supabaseUrl);
    if (uri == null || !uri.hasAuthority || uri.scheme != 'https') {
      throw StateError(
        'SUPABASE_URL must be a valid HTTPS URL. Pass --dart-define=SUPABASE_URL=... when overriding the default.',
      );
    }
    if (supabaseAnonKey.isEmpty) {
      throw StateError(
        'SUPABASE_ANON_KEY must not be empty. Pass --dart-define=SUPABASE_ANON_KEY=... when overriding the default.',
      );
    }
    if (kReleaseMode && uri.scheme != 'https') {
      throw StateError('Supabase must use HTTPS in release builds.');
    }
  }
}
