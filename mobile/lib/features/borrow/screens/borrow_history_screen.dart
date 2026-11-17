import 'dart:async';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatter.dart';
import '../../../shared/widgets/async_states.dart';
import '../../../shared/widgets/review_dialog.dart';
import '../../../shared/widgets/status_chip.dart';
import '../../auth/providers/auth_provider.dart';
import '../data/borrow_repository.dart';
import '../models/borrow_request.dart';

class BorrowHistoryScreen extends StatefulWidget {
  const BorrowHistoryScreen({super.key});

  @override
  State<BorrowHistoryScreen> createState() => _BorrowHistoryScreenState();
}

class _BorrowHistoryScreenState extends State<BorrowHistoryScreen>
    with WidgetsBindingObserver {
  late Future<List<BorrowRequest>> _future;
  Timer? _refreshTimer;
  RealtimeChannel? _requestChannel;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _future = context.read<BorrowRepository>().listRequests();
    final employeeId = context.read<AuthProvider>().user?.id;
    if (employeeId != null) {
      _requestChannel = context.read<BorrowRepository>().subscribeToRequestChanges(
        employeeId: employeeId,
        onChange: () {
          if (mounted) _reload();
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
      if (WidgetsBinding.instance.lifecycleState == AppLifecycleState.resumed) {
        _reload();
      }
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    final requestChannel = _requestChannel;
    if (requestChannel != null) unawaited(requestChannel.unsubscribe());
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) _reload();
  }

  void _reload() {
    setState(() => _future = context.read<BorrowRepository>().listRequests());
  }

  Future<void> _refresh() async {
    final future = context.read<BorrowRepository>().listRequests();
    setState(() => _future = future);
    try {
      await future;
    } on AppException {
      // FutureBuilder displays the request error in the history screen.
    }
  }

  Widget _refreshableState(Widget child) => RefreshIndicator(
    onRefresh: _refresh,
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Borrow Requests')),
      body: FutureBuilder<List<BorrowRequest>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting &&
              !snapshot.hasData) {
            return const LoadingView();
          }
          if (snapshot.hasError) {
            final error = snapshot.error;
            final message = error is AppException
                ? error.userMessage
                : 'Could not load requests.';
            return _refreshableState(
              ErrorView(message: message, onRetry: _reload),
            );
          }
          final requests = snapshot.data ?? [];
          if (requests.isEmpty) {
            return _refreshableState(
              const EmptyState(
                message:
                    'No borrow requests yet. Borrow an item to get started.',
                icon: Icons.receipt_long_outlined,
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              physics: const AlwaysScrollableScrollPhysics(),
              itemCount: requests.length,
              itemBuilder: (context, index) {
                final request = requests[index];
                return _RequestCard(request: request, onChanged: _reload);
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
      builder: (_) => ReviewDialog(
        title: 'Cancel request?',
        subtitle: 'Please review this cancellation before continuing.',
        cancelLabel: 'Keep',
        confirmLabel: 'Cancel request',
        isDestructive: true,
        rows: [
          ReviewDialogRow(label: 'Request', value: request.requestNumber),
          const ReviewDialogRow(
            label: 'Effect',
            value: 'Reserved stock will be released.',
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
          SnackBar(
            content: Text(e.userMessage),
            backgroundColor: RgvColors.danger,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final itemNames = request.items
        .map((i) => i.name ?? i.itemCode ?? '')
        .where((s) => s.isNotEmpty)
        .toList();
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
                    style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 16,
                    ),
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
            if (request.returnLocation != null &&
                request.returnLocation!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                'Returned at: ${request.returnLocation}',
                style: const TextStyle(color: RgvColors.slate, fontSize: 13),
              ),
            ],
            if (request.returnedAt != null) ...[
              const SizedBox(height: 4),
              Text(
                'Returned on: ${formatDate(request.returnedAt)}',
                style: const TextStyle(
                  color: RgvColors.slateLight,
                  fontSize: 13,
                ),
              ),
            ],
            if (request.adminRemarks != null &&
                request.adminRemarks!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                'Admin: ${request.adminRemarks}',
                style: const TextStyle(
                  color: RgvColors.slateLight,
                  fontSize: 13,
                  fontStyle: FontStyle.italic,
                ),
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
