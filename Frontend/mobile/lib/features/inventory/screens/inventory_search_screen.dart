import 'dart:async';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../shared/widgets/async_states.dart';
import '../../borrow/screens/borrow_form_screen.dart';
import '../data/inventory_repository.dart';
import '../models/inventory_item.dart';

/// Browses active Laravel inventory and starts a borrow request for an item.
class InventorySearchScreen extends StatefulWidget {
  const InventorySearchScreen({super.key});

  @override
  State<InventorySearchScreen> createState() => _InventorySearchScreenState();
}

class _InventorySearchScreenState extends State<InventorySearchScreen> {
  final _queryController = TextEditingController();
  Timer? _debounce;
  late Future<List<InventoryItem>> _future;
  String _query = '';

  @override
  void initState() {
    super.initState();
    _future = context.read<InventoryRepository>().search('');
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _queryController.dispose();
    super.dispose();
  }

  void _onQueryChanged(String query) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 300), () {
      if (mounted) _load(query);
    });
  }

  void _load([String? query]) {
    final nextQuery = (query ?? _queryController.text).trim();
    setState(() {
      _query = nextQuery;
      _future = context.read<InventoryRepository>().search(nextQuery);
    });
  }

  Future<void> _refresh() async {
    final future = context.read<InventoryRepository>().search(_query);
    setState(() => _future = future);
    try {
      await future;
    } on AppException {
      // FutureBuilder displays the request error in the inventory screen.
    }
  }

  Future<void> _borrow(InventoryItem item) async {
    await Navigator.push<void>(
      context,
      MaterialPageRoute<void>(builder: (_) => BorrowFormScreen(items: [item])),
    );
  }

  Widget _refreshableState(Widget child) => RefreshIndicator(
    onRefresh: _refresh,
    child: ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        SizedBox(
          height: MediaQuery.of(context).size.height * 0.65,
          child: child,
        ),
      ],
    ),
  );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Inventory')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: TextField(
              controller: _queryController,
              onChanged: _onQueryChanged,
              textInputAction: TextInputAction.search,
              onSubmitted: _load,
              decoration: InputDecoration(
                labelText: 'Search inventory',
                hintText: 'Name, item code, or brand',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _queryController.text.isEmpty
                    ? null
                    : IconButton(
                        tooltip: 'Clear search',
                        onPressed: () {
                          _queryController.clear();
                          _load('');
                        },
                        icon: const Icon(Icons.clear),
                      ),
              ),
            ),
          ),
          Expanded(
            child: FutureBuilder<List<InventoryItem>>(
              future: _future,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting &&
                    !snapshot.hasData) {
                  return const LoadingView(label: 'Loading inventory…');
                }

                if (snapshot.hasError) {
                  final error = snapshot.error;
                  final message = error is AppException
                      ? error.userMessage
                      : 'Could not load inventory.';
                  return _refreshableState(
                    ErrorView(message: message, onRetry: _load),
                  );
                }

                final items = snapshot.data ?? const <InventoryItem>[];
                if (items.isEmpty) {
                  return _refreshableState(
                    EmptyState(
                      message: _query.isEmpty
                          ? 'No active inventory is available.'
                          : 'No inventory matches “$_query”.',
                      icon: Icons.inventory_2_outlined,
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: _refresh,
                  child: ListView.separated(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                    itemCount: items.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 8),
                    itemBuilder: (context, index) => _InventoryTile(
                      item: items[index],
                      onTap: items[index].isAvailable
                          ? () => _borrow(items[index])
                          : null,
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _InventoryTile extends StatelessWidget {
  const _InventoryTile({required this.item, required this.onTap});

  final InventoryItem item;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final details = [
      item.itemCode,
      if ((item.brand ?? '').isNotEmpty) item.brand!,
      if ((item.category ?? '').isNotEmpty) item.category!,
    ].join(' • ');

    return Card(
      margin: EdgeInsets.zero,
      child: ListTile(
        leading: const CircleAvatar(
          backgroundColor: RgvColors.primaryLight,
          child: Icon(Icons.inventory_2_outlined, color: RgvColors.primary),
        ),
        title: Text(item.name, maxLines: 2, overflow: TextOverflow.ellipsis),
        subtitle: Text(
          '$details\n${item.quantity} ${item.unit} · ${item.availabilityLabel}',
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        isThreeLine: true,
        trailing: Icon(
          onTap == null ? Icons.block_outlined : Icons.chevron_right,
          color: onTap == null ? RgvColors.slateLight : RgvColors.primary,
        ),
        onTap: onTap,
      ),
    );
  }
}
