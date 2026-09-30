import '../../../core/network/api_client.dart';
import '../models/user.dart';

/// A successful first-step login that either returns a token immediately or
/// requires an MFA verification code.
sealed class LoginResult {
  const LoginResult();
}

class LoginSuccess extends LoginResult {
  const LoginSuccess({required this.token, required this.user});

  final String token;
  final User user;
}

class LoginMfaRequired extends LoginResult {
  const LoginMfaRequired({required this.mfaToken, required this.mfaType});

  final String mfaToken;
  final String mfaType;
}

class AuthRepository {
  AuthRepository(this._api);

  final ApiClient _api;

  Future<LoginResult> login(String email, String password) async {
    final json = await _api.post('/auth/login', body: {
      'email': email,
      'password': password,
      'device_name': 'Android App',
    });

    if (json['mfa_required'] == true) {
      return LoginMfaRequired(
        mfaToken: json['mfa_token'] as String,
        mfaType: json['mfa_type'] as String? ?? 'totp',
      );
    }

    return LoginSuccess(
      token: json['token'] as String,
      user: User.fromJson(json['user'] as Map<String, dynamic>),
    );
  }

  Future<LoginSuccess> verifyMfa(String code) async {
    final json = await _api.post('/auth/mfa/verify', body: {
      'code': code,
      'device_name': 'Android App',
    });
    return LoginSuccess(
      token: json['token'] as String,
      user: User.fromJson(json['user'] as Map<String, dynamic>),
    );
  }

  Future<void> logout() => _api.post('/auth/logout');

  Future<User> me() async {
    final json = await _api.get('/me');
    return User.fromJson(json['user'] as Map<String, dynamic>);
  }

  Future<User> updateName(String name) async {
    final json = await _api.patch('/profile', body: {'name': name});
    return User.fromJson(json['user'] as Map<String, dynamic>);
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmation,
  }) {
    return _api.put('/password', body: {
      'current_password': currentPassword,
      'new_password': newPassword,
      'new_password_confirmation': confirmation,
    });
  }
}
