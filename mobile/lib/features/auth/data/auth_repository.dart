import 'package:supabase_flutter/supabase_flutter.dart' hide User;

import '../../../core/errors/app_exception.dart';
import '../../../core/errors/supabase_call.dart';
import '../../../core/network/mobile_api_client.dart';
import '../models/user.dart';

sealed class LoginResult {
  const LoginResult();
}

class LoginSuccess extends LoginResult {
  const LoginSuccess({required this.user});

  final User user;
}

class LoginMfaRequired extends LoginResult {
  const LoginMfaRequired({required this.factorId});
  final String factorId;
}

class LoginMfaSetupRequired extends LoginResult {
  const LoginMfaSetupRequired({required this.factorId, required this.secret});
  final String factorId;
  final String secret;
}

/// Authentication is deliberately online-only.
///
/// Supabase Auth establishes the session; restricted database functions then
/// verify that the signed-in identity belongs to an active employee.
class AuthRepository {
  AuthRepository(this._supabase, this._api);

  final SupabaseClient _supabase;
  final MobileApiClient _api;

  Future<bool> get hasSession async => _supabase.auth.currentSession != null;

  Future<LoginResult> login(String email, String password) async {
    final normalized = email.trim().toLowerCase();

    try {
      await supabaseCall(
        () => _supabase.auth.signInWithPassword(
          email: normalized,
          password: password,
        ),
      );
      final user = await me();
      final result = await _resultFor(user);
      return result;
    } on AppException {
      await _clearSupabaseSession();
      rethrow;
    }
  }

  /// Verifies the persisted Supabase Auth session against the employee profile.
  Future<LoginSuccess> restore() async {
    if (_supabase.auth.currentSession == null) {
      throw const AppException('Unauthenticated.', statusCode: 401);
    }
    try {
      return LoginSuccess(user: await me());
    } on AppException catch (error) {
      if (error.isUnauthenticated || error.statusCode == 403) {
        await _clearSupabaseSession();
      }
      rethrow;
    }
  }

  Future<LoginSuccess> verifyMfa(String factorId, String code) async {
    return supabaseCall(() async {
      final challenge = await _supabase.auth.mfa.challenge(factorId: factorId);
      await _supabase.auth.mfa.verify(
        factorId: factorId,
        challengeId: challenge.id,
        code: code,
      );
      final user = await me();
      return LoginSuccess(user: user);
    });
  }

  Future<void> logout() async {
    await supabaseCall(() => _supabase.auth.signOut(scope: SignOutScope.local));
  }

  Future<User> me() async {
    final response = await _api.get('me');
    final user = User.fromJson(
      Map<String, dynamic>.from(response['user'] as Map),
    );
    return user;
  }

  Future<User> updateName(String name) async {
    final response = await _api.patch('profile', body: {'name': name});
    return User.fromJson(Map<String, dynamic>.from(response['user'] as Map));
  }

  Future<void> changePassword({required String newPassword}) async {
    await supabaseCall(
      () => _supabase.auth.updateUser(UserAttributes(password: newPassword)),
    );
  }

  Future<void> _clearSupabaseSession() async {
    try {
      await _supabase.auth.signOut(scope: SignOutScope.local);
    } on AuthException catch (error) {
      if (!error.message.contains('SocketException') &&
          !error.message.contains('ClientException')) {
        rethrow;
      }
    }
  }

  Future<LoginResult> _resultFor(User user) async {
    final assurance = _supabase.auth.mfa.getAuthenticatorAssuranceLevel();
    if (user.mfaEnabled ||
        assurance.nextLevel == AuthenticatorAssuranceLevels.aal2) {
      final factors = await supabaseCall(
        () => _supabase.auth.mfa.listFactors(),
      );
      if (factors.totp.isEmpty) {
        if (!user.mfaEnabled) {
          throw const AppException(
            'No verified Supabase authenticator is available.',
          );
        }
        final enrollment = await supabaseCall(
          () => _supabase.auth.mfa.enroll(
            factorType: FactorType.totp,
            friendlyName: 'RGV Employee App',
          ),
        );
        return LoginMfaSetupRequired(
          factorId: enrollment.id,
          secret: enrollment.totp!.secret,
        );
      }
      if (assurance.currentLevel != AuthenticatorAssuranceLevels.aal2) {
        return LoginMfaRequired(factorId: factors.totp.first.id);
      }
    }
    return LoginSuccess(user: user);
  }
}
