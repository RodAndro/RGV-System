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
  bool _useRecoveryCode = false;
  String? _error;

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  Future<void> _verify() async {
    final code = _codeController.text.trim();
    final validCode = _useRecoveryCode
        ? RegExp(r'^[a-fA-F0-9]{8}-[a-fA-F0-9]{8}$').hasMatch(code)
        : RegExp(r'^\d{6}$').hasMatch(code);
    if (!validCode) {
      setState(
        () => _error = _useRecoveryCode
            ? 'Enter a recovery code in 8-8 format.'
            : 'Enter the 6-digit verification code.',
      );
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
    final setupSecret = auth.mfaSetupSecret;
    return Scaffold(
      appBar: AppBar(
        title: Text(
          setupSecret == null
              ? 'Two-Factor Authentication'
              : 'Set Up Authenticator',
        ),
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
                  Image.asset(
                    'assets/images/RGV-Logo.png',
                    width: 112,
                    height: 112,
                    fit: BoxFit.contain,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    setupSecret == null
                        ? 'Two-factor verification'
                        : 'Set up your authenticator',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    setupSecret == null
                        ? 'Enter the code from your authenticator app to continue.'
                        : 'Add this setup key to your authenticator app, then enter its 6-digit code.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: RgvColors.slateLight),
                  ),
                  const SizedBox(height: 24),
                  if (setupSecret != null) ...[
                    SelectableText(
                      setupSecret,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],
                  if (_error != null) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: RgvColors.dangerBackground,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        _error!,
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: RgvColors.dangerText),
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                  TextField(
                    controller: _codeController,
                    keyboardType: _useRecoveryCode
                        ? TextInputType.text
                        : TextInputType.number,
                    textCapitalization: _useRecoveryCode
                        ? TextCapitalization.characters
                        : TextCapitalization.none,
                    maxLength: _useRecoveryCode ? 17 : 6,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 24, letterSpacing: 8),
                    inputFormatters: [
                      _useRecoveryCode
                          ? FilteringTextInputFormatter.allow(
                              RegExp(r'[a-fA-F0-9-]'),
                            )
                          : FilteringTextInputFormatter.digitsOnly,
                    ],
                    decoration: InputDecoration(
                      labelText: _useRecoveryCode
                          ? 'Recovery code'
                          : '6-digit code',
                      counterText: '',
                    ),
                    onChanged: (_) => setState(() => _error = null),
                    onSubmitted: (_) => _verify(),
                  ),
                  TextButton(
                    onPressed: _submitting
                        ? null
                        : () => setState(() {
                            _useRecoveryCode = !_useRecoveryCode;
                            _codeController.clear();
                            _error = null;
                          }),
                    child: Text(
                      _useRecoveryCode
                          ? 'Use an authenticator code'
                          : 'Use a recovery code',
                    ),
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
                        : Text(
                            setupSecret == null
                                ? 'Verify'
                                : 'Verify and Enable',
                          ),
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
