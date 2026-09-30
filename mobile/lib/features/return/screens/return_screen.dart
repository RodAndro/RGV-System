import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import 'package:uuid/uuid.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatter.dart';
import '../../../shared/widgets/async_states.dart';
import '../data/return_repository.dart';
import '../models/returnable_item.dart';

class ReturnScreen extends StatefulWidget {
  const ReturnScreen({super.key});

  @override
  State<ReturnScreen> createState() => _ReturnScreenState();
}

class _ReturnScreenState extends State<ReturnScreen> {
  late Future<List<ReturnableItem>> _future;

  final _picker = ImagePicker();
  final _uuid = const Uuid();

  final Set<int> _selected = {};
  final Map<int, String> _conditions = {};
  final Map<int, String> _notes = {};
  final Map<int, String> _idempotencyKeys = {};

  String? _photoPath;
  bool _submitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _future = context.read<ReturnRepository>().returnableItems();
  }

  void _reload() {
    setState(() {
      _future = context.read<ReturnRepository>().returnableItems();
      _selected.clear();
      _conditions.clear();
      _notes.clear();
      _photoPath = null;
      _error = null;
    });
  }

  Future<void> _pickPhoto(ImageSource source) async {
    try {
      final file = await _picker.pickImage(
        source: source,
        maxWidth: 2000,
        imageQuality: 80,
      );
      if (file != null) {
        setState(() {
          _photoPath = file.path;
          _error = null;
        });
      }
    } catch (_) {
      _showError('Could not open the camera or gallery.');
    }
  }

  void _showError(String message) {
    setState(() => _error = message);
  }

  Future<void> _submit(List<ReturnableItem> items) async {
    if (_selected.isEmpty) {
      _showError('Select at least one item to return.');
      return;
    }
    if (_photoPath == null) {
      _showError('Attach a photo of the returned item(s).');
      return;
    }

    final selectedItems = items.where((i) => _selected.contains(i.borrowItemId)).toList();
    final byRequest = <int, List<ReturnableItem>>{};
    for (final item in selectedItems) {
      byRequest.putIfAbsent(item.requestId, () => []).add(item);
    }

    setState(() {
      _submitting = true;
      _error = null;
    });

    var anySucceeded = false;
    var anyFailed = false;

    try {
      for (final entry in byRequest.entries) {
        final requestId = entry.key;
        // Reuse a previously issued key so a partial retry stays idempotent.
        final key = _idempotencyKeys.putIfAbsent(requestId, () => _uuid.v4());

        try {
          await context.read<ReturnRepository>().submitReturn(
                requestId: requestId,
                idempotencyKey: key,
                photoPath: _photoPath!,
                items: entry.value
                    .map((i) => (
                          borrowItemId: i.borrowItemId,
                          condition: _conditions[i.borrowItemId] ?? 'good',
                          notes: _notes[i.borrowItemId],
                        ))
                    .toList(),
              );
          anySucceeded = true;
        } on AppException catch (e) {
          anyFailed = true;
          _showError(e.userMessage);
        }
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }

    if (anySucceeded && !anyFailed && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Items returned successfully.'),
          backgroundColor: RgvColors.success,
        ),
      );
      _reload();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Return Items')),
      body: FutureBuilder<List<ReturnableItem>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const LoadingView(label: 'Loading your borrowed items…');
          }
          if (snapshot.hasError) {
            final error = snapshot.error;
            final message = error is AppException ? error.userMessage : 'Could not load items.';
            return ErrorView(message: message, onRetry: _reload);
          }
          final items = snapshot.data ?? [];
          if (items.isEmpty) {
            return const EmptyState(
              message: 'You have no borrowed items to return right now.',
              icon: Icons.assignment_return_outlined,
            );
          }
          return _buildForm(items);
        },
      ),
    );
  }

  Widget _buildForm(List<ReturnableItem> items) {
    return ListView(
      padding: const EdgeInsets.all(16),
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
        const Text(
          'Select the items you are returning',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 8),
        ...items.map((item) => _ReturnItemCard(
              item: item,
              selected: _selected.contains(item.borrowItemId),
              condition: _conditions[item.borrowItemId],
              onChanged: (selected) => setState(() {
                if (selected) {
                  _selected.add(item.borrowItemId);
                } else {
                  _selected.remove(item.borrowItemId);
                }
              }),
              onConditionChanged: (condition) =>
                  setState(() => _conditions[item.borrowItemId] = condition),
              onNotesChanged: (notes) =>
                  setState(() => _notes[item.borrowItemId] = notes),
            )),
        const SizedBox(height: 24),
        const Text(
          'Return proof photo (required)',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 12),
        _PhotoPicker(
          photoPath: _photoPath,
          onCapture: () => _pickPhoto(ImageSource.camera),
          onSelect: () => _pickPhoto(ImageSource.gallery),
        ),
        const SizedBox(height: 24),
        ElevatedButton.icon(
          onPressed: _submitting ? null : () => _submit(items),
          icon: _submitting
              ? const SizedBox(
                  height: 20,
                  width: 20,
                  child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                )
              : const Icon(Icons.check),
          label: Text(_submitting ? 'Submitting…' : 'Submit Return'),
        ),
      ],
    );
  }
}

class _ReturnItemCard extends StatelessWidget {
  const _ReturnItemCard({
    required this.item,
    required this.selected,
    required this.condition,
    required this.onChanged,
    required this.onConditionChanged,
    required this.onNotesChanged,
  });

  final ReturnableItem item;
  final bool selected;
  final String? condition;
  final ValueChanged<bool> onChanged;
  final ValueChanged<String> onConditionChanged;
  final ValueChanged<String> onNotesChanged;

  @override
  Widget build(BuildContext context) {
    final inventory = item.inventory;
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CheckboxListTile(
              contentPadding: EdgeInsets.zero,
              value: selected,
              onChanged: (value) => onChanged(value ?? false),
              title: Text(
                inventory?.name ?? 'Item #${item.borrowItemId}',
                style: const TextStyle(fontWeight: FontWeight.w600),
              ),
              subtitle: Text(
                '${item.requestNumber} · qty ${item.quantity}'
                '${inventory?.unit != null ? ' ${inventory!.unit}' : ''}'
                '\nDue ${formatDate(item.dueDate)}',
              ),
              controlAffinity: ListTileControlAffinity.leading,
            ),
            if (selected) ...[
              const Divider(),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                initialValue: condition ?? 'good',
                decoration: const InputDecoration(labelText: 'Condition on return'),
                items: returnConditions
                    .map((c) => DropdownMenuItem(value: c, child: Text(c)))
                    .toList(),
                onChanged: (value) {
                  if (value != null) onConditionChanged(value);
                },
              ),
              const SizedBox(height: 12),
              TextField(
                decoration: const InputDecoration(
                  labelText: 'Damage / condition notes (optional)',
                  alignLabelWithHint: true,
                ),
                maxLines: 2,
                onChanged: onNotesChanged,
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _PhotoPicker extends StatelessWidget {
  const _PhotoPicker({
    required this.photoPath,
    required this.onCapture,
    required this.onSelect,
  });

  final String? photoPath;
  final VoidCallback onCapture;
  final VoidCallback onSelect;

  @override
  Widget build(BuildContext context) {
    if (photoPath != null) {
      return Column(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Image.file(File(photoPath!), height: 180, fit: BoxFit.cover),
          ),
          const SizedBox(height: 8),
          TextButton.icon(
            onPressed: onSelect,
            icon: const Icon(Icons.refresh),
            label: const Text('Choose a different photo'),
          ),
        ],
      );
    }

    return Row(
      children: [
        Expanded(
          child: OutlinedButton.icon(
            onPressed: onCapture,
            icon: const Icon(Icons.photo_camera_outlined),
            label: const Text('Take photo'),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: onSelect,
            icon: const Icon(Icons.photo_library_outlined),
            label: const Text('Gallery'),
          ),
        ),
      ],
    );
  }
}
