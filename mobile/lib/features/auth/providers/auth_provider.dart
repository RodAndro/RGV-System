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

  bool get isAuthenticated =>
      _status == AuthStatus.authenticated && _user != null;

  /// Restores the Supabase Auth session persisted by supabase_flutter.
  Future<void> restoreSession() async {
    if (!repository.hasSession) {
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return;
    }

    try {
      _acceptLoginResult(await repository.restore());
    } on AppException catch (e) {
      if (e.isUnauthenticated || e.statusCode == 403) {
        await repository.logout();
        _clearSession();
      } else {
        _status = AuthStatus.unauthenticated;
      }
    }
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
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
    try {
      await repository.logout();
    } on AppException {
      // Best-effort: revoke locally even if the server is unreachable.
    } finally {
      _clearSession();
      notifyListeners();
    }
  }

  Future<void> refreshUser() async {
    if (!repository.hasSession) return;
    _user = await repository.me();
    notifyListeners();
  }

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
