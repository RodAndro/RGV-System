import 'package:flutter/foundation.dart';

import '../../../core/errors/app_exception.dart';
import '../data/auth_repository.dart';
import '../models/user.dart';

enum AuthStatus { initializing, unauthenticated, mfaPending, authenticated }

/// Owns the signed-in employee profile, MFA handoff, and logout.
class AuthProvider extends ChangeNotifier {
  AuthProvider({required this.repository});

  final AuthRepository repository;

  AuthStatus _status = AuthStatus.initializing;
  AuthStatus get status => _status;

  User? _user;
  User? get user => _user;

  String? _pendingMfaFactorId;
  String? _mfaSetupSecret;
  String? get mfaSetupSecret => _mfaSetupSecret;
  String? _logoutWarning;
  String? get logoutWarning => _logoutWarning;

  bool get isAuthenticated =>
      _status == AuthStatus.authenticated && _user != null;

  /// Restores a stored session only after Supabase confirms it. When the
  /// backend is unreachable the employee sees the sign-in screen instead.
  Future<void> restoreSession() async {
    try {
      if (!await repository.hasSession) {
        _status = AuthStatus.unauthenticated;
        notifyListeners();
        return;
      }
      _acceptLoginResult(await repository.restore());
    } on AppException catch (error) {
      if (error.isNetworkError) _logoutWarning = error.userMessage;
      _clearSession();
    }
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    _logoutWarning = null;
    final result = await repository.login(email, password);

    _acceptLoginResult(result);
    notifyListeners();
  }

  void _acceptLoginResult(LoginResult result) {
    switch (result) {
      case LoginSuccess(:final user):
        _user = user;
        _pendingMfaFactorId = null;
        _mfaSetupSecret = null;
        _status = AuthStatus.authenticated;
      case LoginMfaRequired(:final factorId):
        _pendingMfaFactorId = factorId;
        _mfaSetupSecret = null;
        _status = AuthStatus.mfaPending;
      case LoginMfaSetupRequired(:final factorId, :final secret):
        _pendingMfaFactorId = factorId;
        _mfaSetupSecret = secret;
        _status = AuthStatus.mfaPending;
    }
  }

  Future<void> verifyMfa(String code) async {
    final factorId = _pendingMfaFactorId;
    if (factorId == null) {
      throw const AppException(
        'The authentication challenge has expired. Sign in again.',
      );
    }
    final result = await repository.verifyMfa(factorId, code);
    _user = result.user;
    _pendingMfaFactorId = null;
    _mfaSetupSecret = null;
    _status = AuthStatus.authenticated;
    notifyListeners();
  }

  Future<void> logout() async {
    _logoutWarning = null;
    try {
      await repository.logout();
    } on AppException catch (error) {
      if (error.isNetworkError || (error.statusCode ?? 0) >= 500) {
        _logoutWarning =
            'You are signed out on this device, but Supabase could not confirm token revocation. Connect to the internet and sign in again to refresh your session.';
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

  /// Returns true when the backend confirmed the change immediately.
  Future<void> updateName(String name) async {
    _user = await repository.updateName(name);
    notifyListeners();
  }

  void _clearSession() {
    _pendingMfaFactorId = null;
    _mfaSetupSecret = null;
    _user = null;
    _status = AuthStatus.unauthenticated;
  }
}
