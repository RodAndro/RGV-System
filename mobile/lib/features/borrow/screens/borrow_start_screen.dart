import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../inventory/data/inventory_repository.dart';
import '../../inventory/screens/qr_scan_screen.dart';
import 'borrow_form_screen.dart';

/// Entry point for the borrow flow: scan a QR code or enter an item code,
/// then hand off the resolved item to [BorrowFormScreen].
class BorrowStartScreen extends StatefulWidget {
  const BorrowStartScreen({super.key});

  @override
  State<BorrowStartScreen> createState() => _BorrowStartScreenState();
}

class _BorrowStartScreenState extends State<BorrowStartScreen> {
  final _codeController = TextEditingController();
  bool _lookingUp = false;
  String? _error;

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  Future<void> _scan() async {
    final code = await Navigator.push<String>(
      context,
      MaterialPageRoute(builder: (_) => const QrScanScreen()),
    );
    if (code == null || code.isEmpty || !mounted) return;
    _codeController.text = code;
    await _lookup(code);
  }

  Future<void> _lookup(String rawCode) async {
    final repo = context.read<InventoryRepository>();
    final code = repo.normalizeCode(rawCode);

    if (code.isEmpty) {
      setState(() => _error = 'Enter or scan an item code.');
      return;
    }

    setState(() {
      _lookingUp = true;
      _error = null;
    });

    try {
      final item = await repo.lookup(code);
      if (!mounted) return;
      await Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => BorrowFormScreen(item: item)),
      );
    } on AppException catch (e) {
      setState(() => _error = e.userMessage);
    } catch (_) {
      setState(() => _error = 'Could not look up this item. Please try again.');
    } finally {
      if (mounted) setState(() => _lookingUp = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Borrow Item')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text(
            'Scan the item\u2019s QR code or enter its code to see current availability.',
            style: TextStyle(color: RgvColors.slateLight),
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: _lookingUp ? null : _scan,
            icon: const Icon(Icons.qr_code_scanner),
            label: const Text('Scan QR Code'),
          ),
          const SizedBox(height: 24),
          const Row(
            children: [
              Expanded(child: Divider()),
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 12),
                child: Text('or enter manually',
                    style: TextStyle(color: RgvColors.slateLight)),
              ),
              Expanded(child: Divider()),
            ],
          ),
          const SizedBox(height: 16),
          if (_error != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFEE2E2),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                _error!,
                style: const TextStyle(color: Color(0xFF991B1B)),
              ),
            ),
            const SizedBox(height: 16),
          ],
          TextField(
            controller: _codeController,
            textCapitalization: TextCapitalization.characters,
            decoration: const InputDecoration(
              labelText: 'Item code',
              hintText: 'e.g. RGV-009',
              prefixIcon: Icon(Icons.tag),
            ),
            onSubmitted: (value) => _lookup(value),
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _lookingUp ? null : () => _lookup(_codeController.text),
            child: _lookingUp
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                  )
                : const Text('Look Up Item'),
          ),
        ],
      ),
    );
  }
}
