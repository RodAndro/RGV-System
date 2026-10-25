import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatter.dart';
import '../../../shared/widgets/async_states.dart';
import '../../../shared/widgets/status_chip.dart';
import '../../account/screens/account_settings_screen.dart';
import '../../auth/providers/auth_provider.dart';
import '../../borrow/screens/borrow_history_screen.dart';
import '../../borrow/screens/borrow_start_screen.dart';
import '../../return/screens/return_screen.dart';
import '../data/dashboard_repository.dart';
import '../models/dashboard_data.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen>
  with WidgetsBindingObserver {
  late Future<DashboardData> _future;
  final Set<int> _dismissedRecentRequestIds = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _future = context.read<DashboardRepository>().fetch();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) _reload();
  }

  void _reload() {
    setState(() => _future = context.read<DashboardRepository>().fetch());
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Image.asset(
              'assets/images/RGV-Logo.png',
              width: 36,
              height: 36,
              fit: BoxFit.contain,
            ),
            const SizedBox(width: 10),
            const Text('Dashboard'),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Sign out',
            icon: const Icon(Icons.logout),
            onPressed: () => _confirmLogout(context),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          _reload();
          await _future.catchError((_) => DashboardData(
                summary: const BorrowSummary(
                  total: 0,
                  pending: 0,
                  approved: 0,
                  borrowed: 0,
                  returned: 0,
                  overdue: 0,
                ),
                recentRequests: const [],
              ));
        },
        child: FutureBuilder<DashboardData>(
          future: _future,
          builder: (context, snapshot) {
            return _DashboardBody(
              data: snapshot.data,
              employeeName: user?.name ?? '',
              isLoading: snapshot.connectionState == ConnectionState.waiting,
              errorMessage: snapshot.hasError
                  ? snapshot.error is AppException
                      ? (snapshot.error! as AppException).userMessage
                      : 'Could not load your dashboard.'
                  : null,
              onRetry: _reload,
              dismissedRequestIds: _dismissedRecentRequestIds,
              onRemoveRequest: (request) {
                setState(() => _dismissedRecentRequestIds.add(request.id));
              },
            );
          },
        ),
      ),
    );
  }

  Future<void> _confirmLogout(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sign out?'),
        content: const Text('You will need to sign in again to use the app.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Sign out'),
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      await context.read<AuthProvider>().logout();
    }
  }
}

class _DashboardBody extends StatelessWidget {
  const _DashboardBody({
    required this.data,
    required this.employeeName,
    required this.dismissedRequestIds,
    required this.onRemoveRequest,
    required this.isLoading,
    required this.errorMessage,
    required this.onRetry,
  });

  final DashboardData? data;
  final String employeeName;
  final Set<int> dismissedRequestIds;
  final ValueChanged<RecentRequest> onRemoveRequest;
  final bool isLoading;
  final String? errorMessage;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final summary = data?.summary;
    final recentRequests = (data?.recentRequests ?? const <RecentRequest>[])
        .where((request) => !dismissedRequestIds.contains(request.id))
        .toList();

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        if (employeeName.isNotEmpty) ...[
          Text(
            'Welcome back,',
            style: const TextStyle(color: RgvColors.slateLight, fontSize: 15),
          ),
          Text(
            employeeName,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: RgvColors.slate,
            ),
          ),
          const SizedBox(height: 16),
        ],
        _ActionTiles(),
        if (isLoading)
          const Padding(
            padding: EdgeInsets.only(top: 24),
            child: LoadingView(label: 'Loading dashboard…'),
          ),
        if (errorMessage != null)
          Padding(
            padding: const EdgeInsets.only(top: 24),
            child: ErrorView(message: errorMessage!, onRetry: onRetry),
          ),
        if (summary != null) ...[
          const SizedBox(height: 24),
          const Text(
            'My borrow requests',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 12),
          _SummaryGrid(summary: summary),
          const SizedBox(height: 24),
          const Text(
            'Recent requests',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 8),
          if (recentRequests.isEmpty)
            const EmptyState(message: 'No recent requests to show.')
          else
            ...recentRequests.map(
              (request) => Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: _RecentRequestTile(
                  request: request,
                  onRemove: () => onRemoveRequest(request),
                ),
              ),
            ),
        ],
      ],
    );
  }
}

class _ActionTiles extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final actions = [
      _ActionTile(
        icon: Icons.qr_code_scanner,
        label: 'Borrow',
        color: RgvColors.primary,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const BorrowStartScreen()),
        ),
      ),
      _ActionTile(
        icon: Icons.assignment_return,
        label: 'Return',
        color: RgvColors.primaryDark,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const ReturnScreen()),
        ),
      ),
      _ActionTile(
        icon: Icons.receipt_long,
        label: 'History',
        color: RgvColors.primary,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const BorrowHistoryScreen()),
        ),
      ),
      _ActionTile(
        icon: Icons.manage_accounts,
        label: 'Account',
        color: RgvColors.amber,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const AccountSettingsScreen()),
        ),
      ),
    ];

    return SizedBox(
      height: 124,
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          for (var index = 0; index < actions.length; index++) ...[
            if (index > 0) const SizedBox(width: 8),
            Expanded(child: actions[index]),
          ],
        ],
      ),
    );
  }
}

class _ActionTile extends StatelessWidget {
  const _ActionTile({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      elevation: 1,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 48,
                height: 48,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(height: 8),
              Text(
                label,
                maxLines: 1,
                softWrap: false,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.center,
                style: const TextStyle(fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SummaryGrid extends StatelessWidget {
  const _SummaryGrid({required this.summary});

  final BorrowSummary summary;

  @override
  Widget build(BuildContext context) {
    final items = [
      ('Total', summary.total, RgvColors.slate),
      ('Pending', summary.pending, RgvColors.amber),
      ('Borrowed', summary.borrowed, RgvColors.primaryDark),
      ('Overdue', summary.overdue, RgvColors.danger),
    ];
    return SizedBox(
      height: 88,
      child: Row(
        children: [
          for (var index = 0; index < items.length; index++) ...[
            if (index > 0) const SizedBox(width: 8),
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 24,
                      height: 3,
                      decoration: BoxDecoration(
                        color: items[index].$3,
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      '${items[index].$2}',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    Text(
                      items[index].$1,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: RgvColors.slateLight,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _RecentRequestTile extends StatelessWidget {
  const _RecentRequestTile({required this.request, required this.onRemove});

  final RecentRequest request;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    return Dismissible(
      key: ValueKey(request.id),
      direction: DismissDirection.endToStart,
      background: const _RemoveRequestBackground(alignment: Alignment.centerLeft),
      secondaryBackground: const _RemoveRequestBackground(
        alignment: Alignment.centerRight,
      ),
      onDismissed: (_) => onRemove(),
      child: Card(
        margin: EdgeInsets.zero,
        child: ListTile(
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          leading: const CircleAvatar(
            backgroundColor: RgvColors.primaryLight,
            child: Icon(Icons.receipt_long, color: RgvColors.primary),
          ),
          title: Text(
            request.requestNumber,
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
          subtitle: Text(
            [
              if (request.items.isNotEmpty) request.items.join(', '),

              if (request.dueDate != null) 'Due ${formatDate(request.dueDate)}',
            ].join(' · '),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          trailing: StatusChip(status: request.status),
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const BorrowHistoryScreen()),
          ),
        ),
      ),
    );
  }
}

class _RemoveRequestBackground extends StatelessWidget {
  const _RemoveRequestBackground({required this.alignment});

  final Alignment alignment;

  @override
  Widget build(BuildContext context) {
    return Container(
      alignment: alignment,
      padding: const EdgeInsets.symmetric(horizontal: 20),
      decoration: BoxDecoration(
        color: RgvColors.danger,
        borderRadius: BorderRadius.circular(16),
      ),
      child: const Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.delete_outline, color: Colors.white),
          SizedBox(width: 8),
          Text(
            'Remove',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}
