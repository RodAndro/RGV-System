/// A user-facing API/application error with an optional HTTP status and
/// per-field validation errors (as returned by Laravel's 422 responses).
class AppException implements Exception {
  const AppException(
    this.message, {
    this.statusCode,
    this.fieldErrors = const {},
    this.isNetworkError = false,
  });

  final String message;
  final int? statusCode;
  final Map<String, List<String>> fieldErrors;
  final bool isNetworkError;

  /// Builds a human-readable message, falling back to per-field errors when
  /// the top-level message is generic.
  String get userMessage {
    if (fieldErrors.isNotEmpty && (message.isEmpty || message == 'The given data was invalid.')) {
      final first = fieldErrors.values.expand((e) => e).toList();
      return first.isNotEmpty ? first.first : message;
    }
    return message;
  }

  bool get isUnauthenticated => statusCode == 401;
  bool get isThrottled => statusCode == 429;

  @override
  String toString() =>
      'AppException($statusCode): $message ${fieldErrors.isNotEmpty ? fieldErrors : ''}';
}
