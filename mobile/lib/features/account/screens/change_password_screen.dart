import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../auth/data/auth_repository.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _currentController = TextEditingController();
  final _newController = TextEditingController();
  final _confirmController = TextEditingController();

  final bool _obscure = true;
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _currentController.dispose();
    _newController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      await context.read<AuthRepository>().changePassword(
            currentPassword: _currentController.text,
            newPassword: _newController.text,
            confirmation: _confirmController.text,
          );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Password updated.'),
            backgroundColor: RgvColors.success,
          ),
        );
        Navigator.pop(context);
      }
    } on AppException catch (e) {
      setState(() => _error = e.userMessage);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Change Password')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Form(
            key: _formKey,
            child: Column(
              children: [
                if (_error != null) ...[
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEE2E2),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(_error!, style: const TextStyle(color: Color(0xFF991B1B))),
                  ),
                  const SizedBox(height: 16),
                ],
                TextFormField(
                  controller: _currentController,
                  obscureText: _obscure,
                  decoration: const InputDecoration(
                    labelText: 'Current password',
                    prefixIcon: Icon(Icons.lock_outline),
                  ),
                  validator: (value) =>
                      (value == null || value.isEmpty) ? 'Enter your current password.' : null,
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _newController,
                  obscureText: _obscure,
                  decoration: const InputDecoration(
                    labelText: 'New password',
                    helperText: 'At least 8 characters with uppercase, lowercase, and a number.',
                    prefixIcon: Icon(Icons.lock_reset),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) return 'Enter a new password.';
                    if (value.length < 8) return 'Password must be at least 8 characters.';
                    if (!value.contains(RegExp(r'[A-Z]'))) return 'Include at least one uppercase letter.';
                    if (!value.contains(RegExp(r'[a-z]'))) return 'Include at least one lowercase letter.';
                    if (!value.contains(RegExp(r'[0-9]'))) return 'Include at least one number.';
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _confirmController,
                  obscureText: _obscure,
                  decoration: const InputDecoration(
                    labelText: 'Confirm new password',
                    prefixIcon: Icon(Icons.lock_reset),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) return 'Confirm your new password.';
                    if (value != _newController.text) return 'Passwords do not match.';
                    return null;
                  },
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: _submitting ? null : _submit,
                  child: _submitting
                      ? const SizedBox(
                          height: 22,
                          width: 22,
                          child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                        )
                      : const Text('Update Password'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
