import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../providers/auth_provider.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _submitting = false;
  bool _obscurePassword = true;
  String? _error;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      await context.read<AuthProvider>().login(
        _emailController.text.trim(),
        _passwordController.text,
      );
      // On success the AuthGate rebuilds and navigates away.
    } on AppException catch (e) {
      setState(() => _error = e.userMessage);
    } catch (_) {
      setState(() => _error = 'Something went wrong. Please try again.');
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final logoutWarning = context.watch<AuthProvider>().logoutWarning;
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Center(
                      child: SizedBox.square(
                        key: const ValueKey('login-logo'),
                        dimension: 160,
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white,
                            border: Border.all(color: RgvColors.cardBorder),
                            boxShadow: const [
                              BoxShadow(
                                color: Color(0x140F172A),
                                blurRadius: 10,
                                offset: Offset(0, 3),
                              ),
                            ],
                          ),
                          child: ClipOval(
                            child: Image.asset(
                              'assets/images/RGV-Logo.png',
                              width: 148,
                              height: 148,
                              fit: BoxFit.contain,
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'RGV Multi-Tech Services',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: RgvColors.slate,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Employee Portal',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: RgvColors.slateLight),
                    ),
                    const SizedBox(height: 32),
                    if (logoutWarning != null) ...[
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: RgvColors.warningBackground,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: RgvColors.warningBorder),
                        ),
                        child: Text(
                          logoutWarning,
                          style: const TextStyle(color: RgvColors.warningText),
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],
                    if (_error != null) ...[
                      _ErrorBanner(message: _error!),
                      const SizedBox(height: 16),
                    ],
                    TextFormField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      autofillHints: const [AutofillHints.email],
                      textInputAction: TextInputAction.next,
                      decoration: const InputDecoration(
                        labelText: 'Email',
                        prefixIcon: Icon(Icons.email_outlined),
                      ),
                      validator: (value) {
                        final email = value?.trim() ?? '';
                        if (email.isEmpty) {
                          return 'Enter your email address.';
                        }
                        if (!email.contains('@')) {
                          return 'Enter a valid email address.';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      autofillHints: const [AutofillHints.password],
                      textInputAction: TextInputAction.done,
                      onFieldSubmitted: (_) => _submit(),
                      onChanged: (value) => setState(() {
                        if (value.isEmpty) _obscurePassword = true;
                      }),
                      decoration: InputDecoration(
                        labelText: 'Password',
                        prefixIcon: const Icon(Icons.lock_outline),
                        suffixIcon: _passwordController.text.isEmpty
                            ? null
                            : IconButton(
                                icon: Icon(
                                  _obscurePassword
                                      ? Icons.visibility_off
                                      : Icons.visibility,
                                ),
                                onPressed: () => setState(
                                  () => _obscurePassword = !_obscurePassword,
                                ),
                              ),
                      ),
                      validator: (value) => (value == null || value.isEmpty)
                          ? 'Enter your password.'
                          : null,
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: _submitting ? null : _submit,
                      child: _submitting
                          ? const SizedBox(
                              height: 22,
                              width: 22,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.5,
                                color: Colors.white,
                              ),
                            )
                          : const Text('Sign In'),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  const _ErrorBanner({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: RgvColors.dangerBackground,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: RgvColors.dangerBorder),
      ),
      child: Row(
        children: [
          const Icon(Icons.error_outline, color: RgvColors.danger, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(color: RgvColors.dangerText, fontSize: 14),
            ),
          ),
        ],
      ),
    );
  }
}
