import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../providers/auth_provider.dart';

class MfaScreen extends StatefulWidget {
  const MfaScreen({super.key});

  @override
  State<MfaScreen> createState() => _MfaScreenState();
}

class _MfaScreenState extends State<MfaScreen> {
  final _codeController = TextEditingController();
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  Future<void> _verify() async {
    final code = _codeController.text.trim();
    if (code.length != 6) {
      setState(() => _error = 'Enter the 6-digit verification code.');
      return;
    }

    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      await context.read<AuthProvider>().verifyMfa(code);
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
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      appBar: AppBar(
        title: const Text('Two-Factor Authentication'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: _submitting
              ? null
              : () async {
                  await auth.logout();
                },
        ),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Icon(Icons.shield_outlined, size: 64, color: RgvColors.blue),
                  const SizedBox(height: 16),
                  const Text(
                    'Enter your verification code',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Your account has two-factor authentication enabled. Enter the code from your authenticator app to continue.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: RgvColors.slateLight),
                  ),
                  const SizedBox(height: 24),
                  if (_error != null) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEE2E2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        _error!,
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: Color(0xFF991B1B)),
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                  TextField(
                    controller: _codeController,
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 24, letterSpacing: 8),
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                    decoration: const InputDecoration(
                      labelText: '6-digit code',
                      counterText: '',
                    ),
                    onChanged: (_) => setState(() => _error = null),
                    onSubmitted: (_) => _verify(),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: _submitting ? null : _verify,
                    child: _submitting
                        ? const SizedBox(
                            height: 22,
                            width: 22,
                            child: CircularProgressIndicator(
                              strokeWidth: 2.5,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Verify'),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
