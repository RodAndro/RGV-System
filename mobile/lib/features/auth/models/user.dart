/// The signed-in employee account returned by the Laravel mobile API.
class User {
  const User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.avatarPath,
    this.isActive = true,
    this.mfaEnabled = false,
    this.role = 'employee',
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? avatarPath;
  final bool isActive;
  final bool mfaEnabled;
  final String role;

  factory User.fromJson(Map<String, dynamic> json) => User(
        id: json['id'] as int,
        name: json['name'] as String? ?? '',
        email: json['email'] as String? ?? '',
        phone: json['phone'] as String?,
        avatarPath: json['avatar_path'] as String?,
        isActive: json['is_active'] as bool? ?? true,
        mfaEnabled: json['mfa_enabled'] as bool? ?? false,
        role: json['role'] as String? ?? 'employee',
      );

  User copyWith({String? name}) => User(
        id: id,
        name: name ?? this.name,
        email: email,
        phone: phone,
        avatarPath: avatarPath,
        isActive: isActive,
        mfaEnabled: mfaEnabled,
        role: role,
      );
}
