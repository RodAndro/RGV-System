import 'package:flutter/foundation.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/storage/token_storage.dart';
import '../data/auth_repository.dart';
import '../models/user.dart';

enum AuthStatus { initializing, unauthenticated, mfaPending, authenticated }

/// Owns the signed-in employee session: token lifecycle, restored sessions,
/// MFA handoff, and logout.
class AuthProvider extends ChangeNotifier {
  AuthProvider({
    required this.storage,
    required this.repository,
  });

  final TokenStorage storage;
  final AuthRepository repository;

  AuthStatus _status = AuthStatus.initializing;
  AuthStatus get status => _status;

  User? _user;
  User? get user => _user;

  String? _accessToken;
  String? _mfaToken;

  /// The bearer token to attach to API requests. While MFA is pending this
  /// returns the short-lived MFA token instead of a (not-yet-issued) access
  /// token, so `/auth/mfa/verify` is authenticated correctly.
  String? get _activeToken => _mfaToken ?? _accessToken;

  String? Function() get tokenProvider => () => _activeToken;

  bool get isAuthenticated => _status == AuthStatus.authenticated && _user != null;

  /// Attempts to restore a previous session from secure storage and validates
  /// it against the server. Falls back to the login screen on any failure.
  Future<void> restoreSession() async {
    final token = await storage.readAccessToken();
    if (token == null) {
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return;
    }

    _accessToken = token;
    try {
      _user = await repository.me();
      _status = AuthStatus.authenticated;
    } on AppException catch (e) {
      // Expired/revoked tokens (401/403) clear the stale session; network
      // errors keep the token so the user can retry without re-entering
      // credentials.
      if (e.isUnauthenticated || e.statusCode == 403) {
        await _clearSession();
      } else {
        _status = AuthStatus.unauthenticated;
        _accessToken = null;
      }
    }
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    final result = await repository.login(email, password);

    switch (result) {
      case LoginSuccess(:final token, :final user):
        _accessToken = token;
        _user = user;
        _status = AuthStatus.authenticated;
        await storage.writeAccessToken(token);
      case LoginMfaRequired(:final mfaToken):
        _mfaToken = mfaToken;
        _status = AuthStatus.mfaPending;
        await storage.writeMfaToken(mfaToken);
    }
    notifyListeners();
  }

  Future<void> verifyMfa(String code) async {
    final result = await repository.verifyMfa(code);
    _accessToken = result.token;
    _user = result.user;
    _mfaToken = null;
    _status = AuthStatus.authenticated;
    await storage.writeAccessToken(result.token);
    await storage.clearMfaToken();
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await repository.logout();
    } on AppException {
      // Best-effort: revoke locally even if the server is unreachable.
    } finally {
      await _clearSession();
      notifyListeners();
    }
  }

  Future<void> refreshUser() async {
    if (_accessToken == null) return;
    _user = await repository.me();
    notifyListeners();
  }

  Future<void> updateName(String name) async {
    _user = await repository.updateName(name);
    notifyListeners();
  }

  Future<void> _clearSession() async {
    _accessToken = null;
    _mfaToken = null;
    _user = null;
    _status = AuthStatus.unauthenticated;
    await storage.clearAll();
  }
}
