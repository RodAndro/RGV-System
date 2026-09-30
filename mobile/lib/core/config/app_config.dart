import 'package:flutter/foundation.dart';

/// Application-wide configuration.
///
/// The API base URL is injectable at build time with
/// `flutter run --dart-define=API_BASE_URL=https://api.example.com/api/v1/mobile`.
class AppConfig {
  const AppConfig._();

  /// Base URL of the Laravel mobile API (no trailing slash).
  ///
  /// `10.0.2.2` is the Android emulator alias for the host machine, so the
  /// default targets a local `php artisan serve` instance during development.
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1/mobile',
  );

  /// Whether the configured base URL uses HTTPS.
  static bool get isHttps => Uri.parse(apiBaseUrl).scheme == 'https';

  /// Guards the "HTTPS required outside local development" rule. Called once
  /// at startup in release/profile builds so a misconfigured HTTP endpoint
  /// fails fast instead of silently leaking credentials.
  static void assertHttpsInProduction() {
    if (kReleaseMode && !isHttps) {
      throw StateError(
        'API_BASE_URL must use HTTPS in release builds. Configured: $apiBaseUrl',
      );
    }
  }
}
