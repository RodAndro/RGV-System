import 'package:flutter/foundation.dart';

/// Configuration for the Laravel REST API this app talks to.
///
/// The app has no database or third-party backend credentials of its own:
/// every request goes through the team's Laravel API.
///
/// Debug builds discover the Laravel API on the current local network.
/// Release builds must provide a stable HTTPS URL with:
///   --dart-define=API_BASE_URL=https://your-deployment.example
class AppConfig {
  const AppConfig._();

  static const String apiBaseUrl = String.fromEnvironment('API_BASE_URL');

  static bool get usesAutomaticDiscovery => apiBaseUrl.trim().isEmpty;

  /// Base path for the mobile API. Versioned so the client never guesses.
  static const String apiPrefix = '/api/v1/mobile';

  static String get apiRoot {
    if (usesAutomaticDiscovery) {
      throw StateError(
        'The local Laravel API address has not been discovered yet.',
      );
    }

    return '${apiBaseUrl.replaceAll(RegExp(r'/+$'), '')}$apiPrefix';
  }

  static void validate() {
    if (usesAutomaticDiscovery) {
      if (kReleaseMode) {
        throw StateError(
          'Release builds must define API_BASE_URL with the deployed HTTPS API URL.',
        );
      }

      return;
    }

    final uri = Uri.tryParse(apiBaseUrl);

    if (uri == null || !uri.hasAuthority) {
      throw StateError(
        'API_BASE_URL must be a valid URL such as https://your-api.example. '
        'Pass --dart-define=API_BASE_URL=... to override it.',
      );
    }

    if (uri.scheme != 'http' && uri.scheme != 'https') {
      throw StateError('API_BASE_URL must use http or https.');
    }

    if (kReleaseMode && uri.scheme != 'https') {
      throw StateError(
        'Release builds must use an HTTPS API_BASE_URL. Pass '
        '--dart-define=API_BASE_URL=https://... when building.',
      );
    }
  }
}
