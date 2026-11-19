import 'package:flutter/foundation.dart';

import '../../../core/errors/app_exception.dart';
import '../data/auth_repository.dart';
import '../models/user.dart';

enum AuthStatus { initializing, unauthenticated, mfaPending, authenticated }

/// Owns the signed-in employee profile, the MFA handoff, and logout.
class AuthProvider extends ChangeNotifier {
  AuthProvider({required this.repository});

  final AuthRepository repository;

  AuthStatus _status = AuthStatus.initializing;
  AuthStatus get status => _status;

  User? _user;
  User? get user => _user;

  /// Present only while an MFA challenge is pending.
  String? _pendingMfaToken;
  String? get logoutWarning => _logoutWarning;
  String? _logoutWarning;

  /// The API rejected our token (expired or revoked elsewhere).
  bool _sessionExpired = false;
  bool get sessionExpired => _sessionExpired;

  bool get isAuthenticated =>
      _status == AuthStatus.authenticated && _user != null;

  /// Restores the stored token, then verifies it against the API.
  ///
  /// If the server cannot be reached the employee stays signed out and sees
  /// the sign-in screen rather than a stale cached profile.
  Future<void> restoreSession() async {
    try {
      final hasToken = await repository.restoreToken();

      if (!hasToken) {
        _status = AuthStatus.unauthenticated;
        notifyListeners();
        return;
      }

      _user = await repository.me();
      _status = AuthStatus.authenticated;
    } on AppException catch (error) {
      if (error.isNetworkError) {
        _logoutWarning = error.userMessage;
      }
      _clearSession();
    } finally {
      notifyListeners();
    }
  }

  Future<void> login(String email, String password) async {
    _logoutWarning = null;
    _sessionExpired = false;

    final result = await repository.login(email, password);
    _acceptLoginResult(result);
    notifyListeners();
  }

  void _acceptLoginResult(LoginResult result) {
    switch (result) {
      case LoginSuccess(:final user):
        _user = user;
        _pendingMfaToken = null;
        _status = AuthStatus.authenticated;
      case LoginMfaRequired(:final mfaToken):
        _pendingMfaToken = mfaToken;
        _status = AuthStatus.mfaPending;
    }
  }

  Future<void> verifyMfa(String code) async {
    if (_pendingMfaToken == null) {
      throw const AppException(
        'The verification challenge has expired. Sign in again.',
      );
    }

    final result = await repository.verifyMfa(code);

    _user = result.user;
    _pendingMfaToken = null;
    _status = AuthStatus.authenticated;
    notifyListeners();
  }

  /// Called by [MobileApiClient] when the API rejects the stored token.
  void handleUnauthenticated() {
    if (_status == AuthStatus.authenticated) {
      _sessionExpired = true;
      _clearSession();
      notifyListeners();
    }
  }

  Future<void> logout() async {
    _logoutWarning = null;

    try {
      await repository.logout();
    } on AppException catch (error) {
      if (error.isNetworkError || (error.statusCode ?? 0) >= 500) {
        _logoutWarning =
            'You are signed out on this device, but the server could not '
            'confirm token revocation. Connect to the internet and sign in '
            'again to refresh your session.';
      }
    } finally {
      _clearSession();
      notifyListeners();
    }
  }

  Future<void> refreshUser() async {
    _user = await repository.me();
    notifyListeners();
  }

  Future<void> updateName(String name) async {
    _user = await repository.updateName(name);
    notifyListeners();
  }

  void clearExpiredNotice() {
    _sessionExpired = false;
    notifyListeners();
  }

  void _clearSession() {
    _pendingMfaToken = null;
    _user = null;
    _status = AuthStatus.unauthenticated;
  }
}
