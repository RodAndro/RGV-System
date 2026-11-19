import 'dart:async';

import 'package:shared_preferences/shared_preferences.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/network/mobile_api_client.dart';
import '../models/user.dart';

sealed class LoginResult {
  const LoginResult();
}

class LoginSuccess extends LoginResult {
  const LoginSuccess({required this.user});

  final User user;
}

/// The account has MFA enabled; the server issued a short-lived token that is
/// only good for `POST /auth/mfa/verify`.
class LoginMfaRequired extends LoginResult {
  const LoginMfaRequired({required this.mfaToken});

  final String mfaToken;
}

/// Authentication against the Laravel API.
///
/// The admin creates the employee in the staff portal; that employee signs in
/// here with the same email and password. Laravel is the single source of
/// identity: it verifies the password, checks the account is active and holds
/// the employee role, then issues a Sanctum bearer token.
class AuthRepository {
  AuthRepository(this._api);

  final MobileApiClient _api;

  static const _tokenKey = 'rgv.api_token';

  /// Restores a stored token so the app can start without a network round trip.
  Future<bool> restoreToken() async {
    final prefs = await SharedPreferences.getInstance();
    final stored = prefs.getString(_tokenKey);

    if (stored == null || stored.isEmpty) return false;

    _api.setToken(stored);
    return true;
  }

  Future<LoginResult> login(String email, String password) async {
    final response = await _api.post(
      'auth/login',
      authenticate: false,
      body: {'email': email.trim().toLowerCase(), 'password': password},
    );

    if (response['mfa_required'] == true) {
      final mfaToken = response['mfa_token']?.toString();

      if (mfaToken == null || mfaToken.isEmpty) {
        throw const AppException(
          'The server did not return a verification token. Please try again.',
        );
      }

      // The MFA-scoped token is only valid for the verify call.
      _api.setToken(mfaToken);
      return LoginMfaRequired(mfaToken: mfaToken);
    }

    return LoginSuccess(user: await _acceptToken(response));
  }

  /// Completes an MFA challenge. `code` is a 6-digit TOTP code or a recovery
  /// code in 8-8 hex format.
  Future<LoginSuccess> verifyMfa(String code) async {
    final response = await _api.post(
      'auth/mfa/verify',
      body: {'code': code.trim()},
    );

    return LoginSuccess(user: await _acceptToken(response));
  }

  Future<void> logout() async {
    try {
      await _api.post('auth/logout');
    } finally {
      await _clearToken();
    }
  }

  Future<User> me() async {
    final response = await _api.get('me');
    return _userFrom(response);
  }

  Future<User> updateName(String name) async {
    final response = await _api.patch('profile', body: {'name': name});
    return _userFrom(response);
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    await _api.put(
      'password',
      body: {
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': newPassword,
      },
    );
  }

  Future<void> _clearToken() async {
    _api.setToken(null);
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }

  /// Persists the bearer token and returns the signed-in profile.
  Future<User> _acceptToken(Map<String, dynamic> response) async {
    final token = response['token']?.toString();

    if (token == null || token.isEmpty) {
      throw const AppException('The server did not return a sign-in token.');
    }

    _api.setToken(token);

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);

    final userJson = response['user'];

    if (userJson is Map) {
      return User.fromJson(Map<String, dynamic>.from(userJson));
    }

    // Some flows return only the token; fetch the profile.
    return me();
  }

  User _userFrom(Map<String, dynamic> response) {
    final user = response['user'];

    if (user is Map) {
      return User.fromJson(Map<String, dynamic>.from(user));
    }

    // /me returns {user: {...}}
    if (response.containsKey('id')) {
      return User.fromJson(response);
    }

    throw const AppException('The server returned an unexpected profile.');
  }
}
