import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../../core/theme/app_theme.dart';

/// Full-screen QR scanner. Pops with the raw scanned payload; the caller is
/// responsible for treating it as an item-code hint.
class QrScanScreen extends StatefulWidget {
  const QrScanScreen({super.key});

  @override
  State<QrScanScreen> createState() => _QrScanScreenState();
}

class _QrScanScreenState extends State<QrScanScreen> {
  final MobileScannerController _controller = MobileScannerController();
  bool _handled = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) {
    if (_handled) return;
    final value = capture.barcodes.isNotEmpty ? capture.barcodes.first.rawValue : null;
    if (value == null || value.isEmpty) return;
    _handled = true;
    Navigator.pop(context, value);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Scan QR Code')),
      backgroundColor: Colors.black,
      body: Column(
        children: [
          Expanded(
            child: MobileScanner(
              controller: _controller,
              onDetect: _onDetect,
              errorBuilder: (context, error, _) => _CameraErrorView(
                error: error,
                onManual: () => Navigator.pop(context),
              ),
            ),
          ),
          Container(
            width: double.infinity,
            color: Colors.white,
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                const Text(
                  'Point your camera at the QR code on the item.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: RgvColors.slate),
                ),
                const SizedBox(height: 12),
                TextButton.icon(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.keyboard_outlined),
                  label: const Text('Enter code manually'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _CameraErrorView extends StatelessWidget {
  const _CameraErrorView({required this.error, required this.onManual});

  final MobileScannerException error;
  final VoidCallback onManual;

  @override
  Widget build(BuildContext context) {
    final code = error.errorCode.name;
    final permissionDenied =
        code == 'permissionDenied' || code == 'cameraAccessDenied';

    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              permissionDenied ? Icons.no_photography : Icons.error_outline,
              color: Colors.white,
              size: 56,
            ),
            const SizedBox(height: 16),
            Text(
              permissionDenied
                  ? 'Camera permission is required to scan QR codes.\n\n'
                      'Enable camera access in your device settings, or enter the '
                      'item code manually instead.'
                  : 'The camera could not be opened. You can still enter the item '
                      'code manually.',
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.white, fontSize: 15),
            ),
            const SizedBox(height: 20),
            OutlinedButton(
              onPressed: onManual,
              style: OutlinedButton.styleFrom(foregroundColor: Colors.white),
              child: const Text('Enter code manually'),
            ),
          ],
        ),
      ),
    );
  }
}
