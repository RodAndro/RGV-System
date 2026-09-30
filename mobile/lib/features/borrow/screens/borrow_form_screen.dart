import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../inventory/models/inventory_item.dart';
import '../data/borrow_repository.dart';

class BorrowFormScreen extends StatefulWidget {
  const BorrowFormScreen({super.key, required this.item});

  final InventoryItem item;

  @override
  State<BorrowFormScreen> createState() => _BorrowFormScreenState();
}

class _BorrowFormScreenState extends State<BorrowFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _reasonController = TextEditingController();

  late DateTime _borrowDate = DateTime.now();
  late DateTime _dueDate = DateTime.now().add(const Duration(days: 7));
  int _quantity = 1;
  bool _submitting = false;

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  int get _available => widget.item.quantity;

  Future<void> _pickBorrowDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _borrowDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        _borrowDate = picked;
        if (!_dueDate.isAfter(_borrowDate)) {
          _dueDate = _borrowDate.add(const Duration(days: 1));
        }
      });
    }
  }

  Future<void> _pickDueDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _dueDate,
      firstDate: _borrowDate.add(const Duration(days: 1)),
      lastDate: DateTime.now().add(const Duration(days: 730)),
    );
    if (picked != null) setState(() => _dueDate = picked);
  }

  Future<void> _reviewAndSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Review request'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _reviewRow('Item', '${widget.item.name} (${widget.item.itemCode})'),
            _reviewRow('Quantity', '$_quantity ${widget.item.unit}'),
            _reviewRow('Borrow date', DateFormat('MMM d, yyyy').format(_borrowDate)),
            _reviewRow('Due date', DateFormat('MMM d, yyyy').format(_dueDate)),
            _reviewRow('Reason', _reasonController.text.trim()),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Edit'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Submit'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    setState(() => _submitting = true);
    try {
      final request = await context.read<BorrowRepository>().createRequest(
            borrowDate: DateFormat('yyyy-MM-dd').format(_borrowDate),
            dueDate: DateFormat('yyyy-MM-dd').format(_dueDate),
            reason: _reasonController.text.trim(),
            items: [(inventoryId: widget.item.id, quantity: _quantity)],
          );

      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => _BorrowSuccessScreen(
            requestNumber: request.requestNumber,
          ),
        ),
      );
    } on AppException catch (e) {
      _showError(e.userMessage);
    } catch (_) {
      _showError('Could not submit your request. Please try again.');
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: RgvColors.danger,
      ),
    );
  }

  Widget _reviewRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: const TextStyle(color: RgvColors.slateLight)),
          ),
          Expanded(
            child: Text(value, style: const TextStyle(fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final available = _available > 0 && widget.item.isAvailable;

    return Scaffold(
      appBar: AppBar(title: const Text('Request to Borrow')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _ItemHeader(item: widget.item),
          const SizedBox(height: 16),
          if (!available)
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.warning_amber, color: RgvColors.amber),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'This item is currently unavailable (${widget.item.status}).',
                      style: const TextStyle(color: Color(0xFF92400E)),
                    ),
                  ),
                ],
              ),
            )
          else
            Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Quantity (max $_available ${widget.item.unit})',
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      IconButton.outlined(
                        onPressed: _quantity > 1
                            ? () => setState(() => _quantity--)
                            : null,
                        icon: const Icon(Icons.remove),
                      ),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: Text(
                          '$_quantity',
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      IconButton.outlined(
                        onPressed: _quantity < _available
                            ? () => setState(() => _quantity++)
                            : null,
                        icon: const Icon(Icons.add),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: const Icon(Icons.event, color: RgvColors.blue),
                    title: const Text('Borrow date'),
                    subtitle: Text(DateFormat('MMM d, yyyy').format(_borrowDate)),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: _pickBorrowDate,
                  ),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: const Icon(Icons.event_available, color: RgvColors.blue),
                    title: const Text('Due date'),
                    subtitle: Text(DateFormat('MMM d, yyyy').format(_dueDate)),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: _pickDueDate,
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _reasonController,
                    maxLines: 4,
                    maxLength: 2000,
                    decoration: const InputDecoration(
                      labelText: 'Reason for borrowing',
                      alignLabelWithHint: true,
                    ),
                    validator: (value) {
                      final reason = value?.trim() ?? '';
                      if (reason.isEmpty) return 'Enter a reason for borrowing.';
                      return null;
                    },
                  ),
                  const SizedBox(height: 8),
                  ElevatedButton(
                    onPressed: _submitting ? null : _reviewAndSubmit,
                    child: _submitting
                        ? const SizedBox(
                            height: 22,
                            width: 22,
                            child: CircularProgressIndicator(
                              strokeWidth: 2.5,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Review & Submit'),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

class _ItemHeader extends StatelessWidget {
  const _ItemHeader({required this.item});

  final InventoryItem item;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: RgvColors.blueLight,
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(Icons.inventory_2_outlined, color: RgvColors.blue),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.name,
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                  ),
                  if (item.brand != null)
                    Text('${item.brand}', style: const TextStyle(color: RgvColors.slateLight)),
                  Text(
                    '${item.itemCode} · ${item.category ?? 'Uncategorized'}',
                    style: const TextStyle(color: RgvColors.slateLight, fontSize: 13),
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '${item.quantity}',
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
                ),
                Text('${item.unit} available', style: const TextStyle(fontSize: 12)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _BorrowSuccessScreen extends StatelessWidget {
  const _BorrowSuccessScreen({required this.requestNumber});

  final String requestNumber;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Request Submitted')),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.check_circle, color: RgvColors.success, size: 72),
              const SizedBox(height: 16),
              const Text(
                'Borrow request submitted',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 8),
              Text(
                'Request number',
                style: const TextStyle(color: RgvColors.slateLight),
              ),
              const SizedBox(height: 4),
              Text(
                requestNumber,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                  color: RgvColors.blue,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Status: Pending approval',
                style: TextStyle(color: RgvColors.amber, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => Navigator.of(context).popUntil((r) => r.isFirst),
                child: const Text('Back to Dashboard'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
