import 'package:supabase_flutter/supabase_flutter.dart' hide User;

import '../../../core/errors/app_exception.dart';
import '../../../core/errors/supabase_call.dart';
import '../models/user.dart';

/// A successful first-step login that either returns a token immediately or
/// requires an MFA verification code.
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

class AuthRepository {
  AuthRepository(this._supabase);

  final SupabaseClient _supabase;

  bool get hasSession => _supabase.auth.currentSession != null;

  Future<LoginResult> login(String email, String password) async {
    return supabaseCall(() async {
      await _supabase.auth.signInWithPassword(email: email, password: password);
      try {
        return await _resultFor(await _employee());
      } on AppException {
        await _supabase.auth.signOut();
        rethrow;
      }
    });
  }

  Future<LoginResult> restore() => supabaseCall(() async {
    return _resultFor(await _employee());
  });

  Future<LoginSuccess> verifyMfa(String factorId, String code) async {
    return supabaseCall(() async {
      final challenge = await _supabase.auth.mfa.challenge(factorId: factorId);
      await _supabase.auth.mfa.verify(
        factorId: factorId,
        challengeId: challenge.id,
        code: code,
      );
      return LoginSuccess(user: await _employee());
    });
  }

  Future<void> logout() => supabaseCall(
        () => _supabase.auth.signOut(scope: SignOutScope.local),
      );

  Future<User> me() => _employee();

  Future<User> updateName(String name) async {
    return supabaseCall(() async {
      final json = await _supabase.rpc(
        'mobile_update_employee_name',
        params: {'p_name': name},
      );
      return User.fromJson(Map<String, dynamic>.from(json as Map));
    });
  }

  Future<void> changePassword({required String newPassword}) =>
      supabaseCall(() async {
        await _supabase.auth.updateUser(UserAttributes(password: newPassword));
      });

  Future<User> _employee() => supabaseCall(() async {
    final json = await _supabase.rpc('mobile_employee_profile');
    return User.fromJson(Map<String, dynamic>.from(json as Map));
  });

  Future<LoginResult> _resultFor(User user) async {
    final assurance = _supabase.auth.mfa.getAuthenticatorAssuranceLevel();
    if (user.mfaEnabled ||
        assurance.nextLevel == AuthenticatorAssuranceLevels.aal2) {
      final factors = await _supabase.auth.mfa.listFactors();
      if (factors.totp.isEmpty) {
        if (!user.mfaEnabled) {
          throw const AppException(
            'No verified Supabase authenticator is available.',
          );
        }
        final enrollment = await _supabase.auth.mfa.enroll(
          factorType: FactorType.totp,
          friendlyName: 'RGV Employee App',
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
