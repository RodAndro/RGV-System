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

class _DashboardScreenState extends State<DashboardScreen> {
  late Future<DashboardData> _future;

  @override
  void initState() {
    super.initState();
    _future = context.read<DashboardRepository>().fetch();
  }

  void _reload() {
    setState(() => _future = context.read<DashboardRepository>().fetch());
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
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
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const LoadingView(label: 'Loading dashboard…');
            }
            if (snapshot.hasError) {
              final error = snapshot.error;
              final message = error is AppException
                  ? error.userMessage
                  : 'Could not load your dashboard.';
              return ErrorView(message: message, onRetry: _reload);
            }
            return _DashboardBody(
              data: snapshot.data!,
              employeeName: user?.name ?? '',
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
  const _DashboardBody({required this.data, required this.employeeName});

  final DashboardData data;
  final String employeeName;

  @override
  Widget build(BuildContext context) {
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
        const SizedBox(height: 24),
        const Text(
          'My borrow requests',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 12),
        _SummaryGrid(summary: data.summary),
        const SizedBox(height: 24),
        const Text(
          'Recent requests',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 8),
        if (data.recentRequests.isEmpty)
          const EmptyState(message: 'No borrow requests yet. Start by borrowing an item.')
        else
          ...data.recentRequests.map(
            (r) => _RecentRequestTile(request: r),
          ),
      ],
    );
  }
}

class _ActionTiles extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _ActionTile(
            icon: Icons.qr_code_scanner,
            label: 'Borrow',
            color: RgvColors.blue,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const BorrowStartScreen()),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _ActionTile(
            icon: Icons.assignment_return,
            label: 'Return',
            color: RgvColors.blueDark,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ReturnScreen()),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _ActionTile(
            icon: Icons.manage_accounts,
            label: 'Account',
            color: RgvColors.amber,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const AccountSettingsScreen()),
            ),
          ),
        ),
      ],
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
          padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 8),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 30),
              ),
              const SizedBox(height: 10),
              Text(
                label,
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
      ('Borrowed', summary.borrowed, RgvColors.blueDark),
      ('Overdue', summary.overdue, RgvColors.danger),
    ];
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 2.2,
      children: items.map((e) {
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
          ),
          child: Row(
            children: [
              Container(
                width: 4,
                height: double.infinity,
                decoration: BoxDecoration(
                  color: e.$3,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
              const SizedBox(width: 12),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    '${e.$2}',
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
                  ),
                  Text(
                    e.$1,
                    style: const TextStyle(color: RgvColors.slateLight, fontSize: 13),
                  ),
                ],
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}

class _RecentRequestTile extends StatelessWidget {
  const _RecentRequestTile({required this.request});

  final RecentRequest request;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        leading: const CircleAvatar(
          backgroundColor: RgvColors.blueLight,
          child: Icon(Icons.receipt_long, color: RgvColors.blue),
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
    );
  }
}
