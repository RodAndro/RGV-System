import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatter.dart';
import '../../../shared/widgets/async_states.dart';
import '../../../shared/widgets/status_chip.dart';
import '../data/borrow_repository.dart';
import '../models/borrow_request.dart';

class BorrowHistoryScreen extends StatefulWidget {
  const BorrowHistoryScreen({super.key});

  @override
  State<BorrowHistoryScreen> createState() => _BorrowHistoryScreenState();
}

class _BorrowHistoryScreenState extends State<BorrowHistoryScreen> {
  late Future<List<BorrowRequest>> _future;

  @override
  void initState() {
    super.initState();
    _future = context.read<BorrowRepository>().listRequests();
  }

  void _reload() {
    setState(() => _future = context.read<BorrowRepository>().listRequests());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Borrow Requests')),
      body: FutureBuilder<List<BorrowRequest>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const LoadingView();
          }
          if (snapshot.hasError) {
            final error = snapshot.error;
            final message = error is AppException ? error.userMessage : 'Could not load requests.';
            return ErrorView(message: message, onRetry: _reload);
          }
          final requests = snapshot.data ?? [];
          if (requests.isEmpty) {
            return const EmptyState(
              message: 'No borrow requests yet. Borrow an item to get started.',
              icon: Icons.receipt_long_outlined,
            );
          }
          return RefreshIndicator(
            onRefresh: () async => _reload(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: requests.length,
              itemBuilder: (context, index) {
                final request = requests[index];
                return _RequestCard(
                  request: request,
                  onChanged: _reload,
                );
              },
            ),
          );
        },
      ),
    );
  }
}

class _RequestCard extends StatelessWidget {
  const _RequestCard({required this.request, required this.onChanged});

  final BorrowRequest request;
  final VoidCallback onChanged;

  Future<void> _cancel(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel request?'),
        content: Text('Cancel ${request.requestNumber}? Reserved stock will be released.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Keep'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Cancel request'),
          ),
        ],
      ),
    );

    if (confirmed != true || !context.mounted) return;

    try {
      await context.read<BorrowRepository>().cancelRequest(request.id);
      onChanged();
    } on AppException catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.userMessage), backgroundColor: RgvColors.danger),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final itemNames = request.items.map((i) => i.name ?? i.itemCode ?? '').where((s) => s.isNotEmpty).toList();
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    request.requestNumber,
                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
                  ),
                ),
                StatusChip(status: request.status),
              ],
            ),
            const SizedBox(height: 8),
            if (itemNames.isNotEmpty)
              Text(
                itemNames.join(', '),
                style: const TextStyle(color: RgvColors.slate),
              ),
            const SizedBox(height: 8),
            Text(
              '${formatDate(request.borrowDate)} → ${formatDate(request.dueDate)}',
              style: const TextStyle(color: RgvColors.slateLight, fontSize: 13),
            ),
            if (request.adminRemarks != null && request.adminRemarks!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                'Admin: ${request.adminRemarks}',
                style: const TextStyle(color: RgvColors.slateLight, fontSize: 13, fontStyle: FontStyle.italic),
              ),
            ],
            if (request.status == 'pending') ...[
              const SizedBox(height: 12),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                  onPressed: () => _cancel(context),
                  child: const Text('Cancel request'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
