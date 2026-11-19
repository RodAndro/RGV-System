/// The signed-in employee profile returned by the mobile API.
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

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'email': email,
    'phone': phone,
    'avatar_path': avatarPath,
    'is_active': isActive,
    'mfa_enabled': mfaEnabled,
    'role': role,
  };

  User copyWith({
    String? name,
    String? email,
    String? phone,
    String? avatarPath,
    bool? isActive,
    bool? mfaEnabled,
    String? role,
  }) => User(
    id: id,
    name: name ?? this.name,
    email: email ?? this.email,
    phone: phone ?? this.phone,
    avatarPath: avatarPath ?? this.avatarPath,
    isActive: isActive ?? this.isActive,
    mfaEnabled: mfaEnabled ?? this.mfaEnabled,
    role: role ?? this.role,
  );
}
