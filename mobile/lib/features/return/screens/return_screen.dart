import 'dart:async';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:uuid/uuid.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatter.dart';
import '../../../shared/widgets/async_states.dart';
import '../../../shared/widgets/status_chip.dart';
import '../../auth/providers/auth_provider.dart';
import '../data/return_repository.dart';
import '../models/returnable_item.dart';

class ReturnScreen extends StatefulWidget {
  const ReturnScreen({super.key});

  @override
  State<ReturnScreen> createState() => _ReturnScreenState();
}

class _ReturnScreenState extends State<ReturnScreen>
    with WidgetsBindingObserver {
  late Future<List<ReturnableItem>> _future;
  Timer? _refreshTimer;
  RealtimeChannel? _requestChannel;

  final _uuid = const Uuid();
  final _formKey = GlobalKey<FormState>();

  final Set<int> _selected = {};
  final Map<int, String> _conditions = {};
  final Map<int, String> _notes = {};
  final Map<int, String> _idempotencyKeys = {};

  final TextEditingController _locationController = TextEditingController();
  bool _submitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _future = context.read<ReturnRepository>().returnableItems();
    final employeeId = context.read<AuthProvider>().user?.id;
    if (employeeId != null) {
      _requestChannel = context.read<ReturnRepository>().subscribeToRequestChanges(
        employeeId: employeeId,
        onChange: () {
          if (mounted && !_submitting && _selected.isEmpty) {
            _refreshItems();
          }
        },
        onError: (_) {
          if (!mounted) return;
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text(
                'Live updates are unavailable. The list will continue refreshing periodically.',
              ),
            ),
          );
        },
      );
    }
    _refreshTimer = Timer.periodic(const Duration(seconds: 30), (_) {
      if (WidgetsBinding.instance.lifecycleState == AppLifecycleState.resumed &&
          mounted &&
          !_submitting &&
          _selected.isEmpty) {
        _refreshItems();
      }
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    final requestChannel = _requestChannel;
    if (requestChannel != null) unawaited(requestChannel.unsubscribe());
    WidgetsBinding.instance.removeObserver(this);
    _locationController.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed &&
        !_submitting &&
        _selected.isEmpty) {
      _refreshItems();
    }
  }

  void _reload() {
    setState(() {
      _future = context.read<ReturnRepository>().returnableItems();
      _selected.clear();
      _conditions.clear();
      _notes.clear();
      _locationController.clear();
      _error = null;
    });
  }

  Future<void> _refreshItems() async {
    final future = context.read<ReturnRepository>().returnableItems();
    setState(() => _future = future);
    try {
      await future;
    } on AppException {
      // FutureBuilder displays the request error in the return screen.
    }
  }

  Widget _refreshableState(Widget child) => RefreshIndicator(
    onRefresh: _refreshItems,
    child: ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        SizedBox(
          height: MediaQuery.of(context).size.height * 0.75,
          child: child,
        ),
      ],
    ),
  );

  Widget _refreshableItems(List<ReturnableItem> items) =>
      RefreshIndicator(onRefresh: _refreshItems, child: _buildForm(items));

  Widget _buildReturnState(AsyncSnapshot<List<ReturnableItem>> snapshot) {
    if (snapshot.connectionState == ConnectionState.waiting) {
      return const LoadingView(label: 'Loading your borrowed items…');
    }
    if (snapshot.hasError) {
      final error = snapshot.error;
      final message = error is AppException
          ? error.userMessage
          : 'Could not load items.';
      return _refreshableState(ErrorView(message: message, onRetry: _reload));
    }
    final items = snapshot.data ?? [];
    if (items.isEmpty) {
      return _refreshableState(
        const EmptyState(
          message: 'No borrowed items are approved for return right now.',
          icon: Icons.assignment_return_outlined,
        ),
      );
    }
    return _refreshableItems(items);
  }

  void _showError(String message) {
    setState(() => _error = message);
  }

  Future<void> _submit(List<ReturnableItem> items) async {
    if (_selected.isEmpty) {
      _showError('Select at least one item to return.');
      return;
    }

    final selectedItems = items
        .where((i) => _selected.contains(i.borrowItemId))
        .toList();
    if (!(_formKey.currentState?.validate() ?? false)) return;
    final returnLocation = _locationController.text.trim();

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
            returnLocation: returnLocation,
            items: entry.value
                .map(
                  (i) => (
                    borrowItemId: i.borrowItemId,
                    condition: _conditions[i.borrowItemId] ?? 'good',
                    notes: _notes[i.borrowItemId],
                  ),
                )
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
        SnackBar(
          content: const Text('Items returned successfully.'),
          backgroundColor: RgvColors.success,
        ),
      );
      _reload();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Return Items'),
        actions: [
          IconButton(
            tooltip: 'Refresh borrowed items',
            onPressed: _submitting ? null : _refreshItems,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: FutureBuilder<List<ReturnableItem>>(
        future: _future,
        builder: (context, snapshot) => _buildReturnState(snapshot),
      ),
    );
  }

  Widget _buildForm(List<ReturnableItem> items) {
    return Form(
      key: _formKey,
      autovalidateMode: AutovalidateMode.onUserInteraction,
      child: ListView(
        padding: const EdgeInsets.all(16),
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          if (_error != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: RgvColors.dangerBackground,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                _error!,
                style: const TextStyle(color: RgvColors.dangerText),
              ),
            ),
            const SizedBox(height: 16),
          ],
          const Text(
            'Select the items you are returning',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 8),
          ...items.map(
            (item) => _ReturnItemCard(
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
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'Return location',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 12),
          TextFormField(
            key: const ValueKey('return-location-field'),
            controller: _locationController,
            textInputAction: TextInputAction.done,
            maxLength: 255,
            decoration: const InputDecoration(
              label: Text.rich(
                TextSpan(
                  children: [
                    TextSpan(text: 'Return location'),
                    TextSpan(
                      text: ' *',
                      style: TextStyle(color: RgvColors.danger),
                    ),
                  ],
                ),
              ),
              hintText: 'e.g., Main Warehouse',
              helperText: 'Required',
              prefixIcon: Icon(Icons.location_on_outlined),
            ),
            validator: (value) => value == null || value.trim().isEmpty
                ? 'Return location is required.'
                : null,
            onChanged: (_) {
              if (_error != null) setState(() => _error = null);
            },
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _submitting ? null : () => _submit(items),
            icon: _submitting
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.5,
                      color: Colors.white,
                    ),
                  )
                : const Icon(Icons.check),
            label: Text(_submitting ? 'Submitting…' : 'Submit Return'),
          ),
        ],
      ),
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
              title: Row(
                children: [
                  Expanded(
                    child: Text(
                      inventory?.name ?? 'Item #${item.borrowItemId}',
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                  ),
                  const SizedBox(width: 8),
                  StatusChip(status: item.displayStatus),
                ],
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
                decoration: const InputDecoration(
                  labelText: 'Condition on return',
                ),
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
