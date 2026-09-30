import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Persists the Sanctum bearer token (and the temporary MFA token) in the
/// platform keychain/keystore so sessions survive app restarts.
class TokenStorage {
  TokenStorage([FlutterSecureStorage? storage])
      : _storage = storage ?? const FlutterSecureStorage();

  static const _accessTokenKey = 'rgv_access_token';
  static const _mfaTokenKey = 'rgv_mfa_token';

  final FlutterSecureStorage _storage;

  Future<String?> readAccessToken() => _storage.read(key: _accessTokenKey);

  Future<void> writeAccessToken(String token) =>
      _storage.write(key: _accessTokenKey, value: token);

  Future<String?> readMfaToken() => _storage.read(key: _mfaTokenKey);

  Future<void> writeMfaToken(String token) =>
      _storage.write(key: _mfaTokenKey, value: token);

  Future<void> clearAccessToken() => _storage.delete(key: _accessTokenKey);

  Future<void> clearMfaToken() => _storage.delete(key: _mfaTokenKey);

  Future<void> clearAll() async {
    await Future.wait([clearAccessToken(), clearMfaToken()]);
  }
}
