import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:rgv_employee_app/core/network/mobile_api_client.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/features/auth/data/auth_repository.dart';
import 'package:rgv_employee_app/features/auth/models/user.dart';
import 'package:rgv_employee_app/features/auth/providers/auth_provider.dart';
import 'package:rgv_employee_app/features/dashboard/data/dashboard_repository.dart';
import 'package:rgv_employee_app/features/dashboard/models/dashboard_data.dart';
import 'package:rgv_employee_app/features/dashboard/screens/dashboard_screen.dart';
import 'package:supabase_flutter/supabase_flutter.dart' hide User;

/// Returns an already verified session without touching the network, so the
/// dashboard can be rendered in a widget test.
class _StubAuthRepository extends AuthRepository {
  _StubAuthRepository(super.supabase, super.api);

  @override
  Future<LoginResult> login(String email, String password) async =>
      const LoginSuccess(
        user: User(id: 7, name: 'Juan Dela Cruz', email: 'employee@rgv.test'),
      );
}

class _StubDashboardRepository extends DashboardRepository {
  _StubDashboardRepository(this.data, SupabaseClient supabase)
    : super(MobileApiClient(supabase: supabase));

  DashboardData data;
  final List<int> dismissedRequestIds = [];
  int fetchCount = 0;
  Completer<DashboardData>? pendingFetch;

  @override
  Future<DashboardData> fetch() {
    fetchCount++;
    final pending = pendingFetch;
    pendingFetch = null;
    return pending?.future ?? Future.value(data);
  }

  @override
  Future<void> dismissRecentRequest(int requestId) async {
    dismissedRequestIds.add(requestId);
  }
}

void main() {
  testWidgets('dashboard renders the latest server data', (tester) async {
    tester.view.physicalSize = const Size(390, 844);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final supabase = SupabaseClient(
      'https://example.supabase.co',
      'test-publishable-key',
      authOptions: const AuthClientOptions(autoRefreshToken: false),
    );
    final api = MobileApiClient(supabase: supabase);

    final auth = AuthProvider(repository: _StubAuthRepository(supabase, api));
    await auth.login('employee@rgv.test', 'Secret123');
    expect(auth.status, AuthStatus.authenticated);

    const data = DashboardData(
      summary: BorrowSummary(
        total: 2,
        pending: 1,
        approved: 1,
        borrowed: 0,
        returned: 0,
        overdue: 0,
      ),
      recentRequests: [
        RecentRequest(
          id: 5,
          requestNumber: 'BR-5',
          status: 'pending',
          borrowDate: '2026-11-01',
          dueDate: '2026-11-08',
          items: ['Handheld Jackhammer'],
        ),
      ],
    );
    final dashboardRepository = _StubDashboardRepository(data, supabase);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AuthProvider>.value(value: auth),
          Provider<DashboardRepository>.value(value: dashboardRepository),
        ],
        child: MaterialApp(
          theme: buildAppTheme(),
          home: const DashboardScreen(),
        ),
      ),
    );
    await tester.pump();
    await tester.pump();

    expect(find.text('Juan Dela Cruz'), findsOneWidget);
    expect(find.text('BR-5'), findsOneWidget);
    expect(find.text('Pending'), findsWidgets);
    final metricLabels = ['Total', 'Pending', 'Borrowed', 'Overdue'];
    final metricTopPositions = metricLabels
        .map(
          (label) =>
              tester.getTopLeft(find.byKey(ValueKey('metric-card-$label'))).dy,
        )
        .toList();
    expect(
      metricTopPositions.every(
        (position) => (position - metricTopPositions.first).abs() < 1,
      ),
      isTrue,
    );

    dashboardRepository.data = const DashboardData(
      summary: BorrowSummary(
        total: 3,
        pending: 2,
        approved: 1,
        borrowed: 0,
        returned: 0,
        overdue: 0,
      ),
      recentRequests: [
        RecentRequest(
          id: 6,
          requestNumber: 'BR-6',
          status: 'pending',
          borrowDate: '2026-11-02',
          dueDate: '2026-11-09',
          items: ['Portable Generator'],
        ),
      ],
    );
    final refreshCompleter = Completer<DashboardData>();
    dashboardRepository.pendingFetch = refreshCompleter;
    await tester.drag(find.byType(ListView).first, const Offset(0, 400));
    await tester.pump();

    expect(find.text('Loading dashboard…'), findsNothing);
    expect(find.text('BR-5'), findsOneWidget);

    refreshCompleter.complete(dashboardRepository.data);
    await tester.pumpAndSettle();

    expect(dashboardRepository.fetchCount, 2);
    expect(find.text('BR-5'), findsNothing);
    expect(find.text('BR-6'), findsOneWidget);

    await tester.ensureVisible(find.text('BR-6'));
    await tester.drag(find.text('BR-6'), const Offset(-500, 0));
    await tester.pumpAndSettle();

    expect(dashboardRepository.dismissedRequestIds, [6]);
    expect(find.text('BR-6'), findsNothing);
  });
}
