import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:rgv_employee_app/core/network/mobile_api_client.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/features/auth/data/auth_repository.dart';
import 'package:rgv_employee_app/features/auth/providers/auth_provider.dart';
import 'package:rgv_employee_app/features/borrow/models/borrow_request.dart';
import 'package:rgv_employee_app/features/return/data/return_repository.dart';
import 'package:rgv_employee_app/features/return/models/returnable_item.dart';
import 'package:rgv_employee_app/features/return/screens/return_screen.dart';
import 'package:supabase_flutter/supabase_flutter.dart' hide User;

class _StubReturnRepository extends ReturnRepository {
  _StubReturnRepository(this.supabase)
    : super(MobileApiClient(supabase: supabase));

  final SupabaseClient supabase;
  final submittedLocations = <String>[];

  @override
  Future<List<ReturnableItem>> returnableItems() async => const [
    ReturnableItem(
      borrowItemId: 10,
      requestNumber: 'BR-10',
      requestId: 2,
      status: 'borrowed',
      quantity: 1,
      inventory: ReturnableInventory(name: 'HVAC Leak Detector'),
    ),
  ];

  @override
  RealtimeChannel subscribeToRequestChanges({
    required int employeeId,
    required void Function() onChange,
    required void Function(Object?) onError,
  }) => supabase.channel('return-screen-test');

  @override
  Future<BorrowRequest> submitReturn({
    required int requestId,
    required String idempotencyKey,
    required String returnLocation,
    required List<({int borrowItemId, String condition, String? notes})> items,
  }) async {
    submittedLocations.add(returnLocation);
    return BorrowRequest(
      id: requestId,
      requestNumber: 'BR-$requestId',
      status: 'returned',
    );
  }
}

void main() {
  testWidgets('return location is required before return submission', (
    tester,
  ) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final supabase = SupabaseClient(
      'https://example.supabase.co',
      'test-publishable-key',
      authOptions: const AuthClientOptions(autoRefreshToken: false),
    );
    final api = MobileApiClient(supabase: supabase);
    final auth = AuthProvider(repository: AuthRepository(supabase, api));
    final repository = _StubReturnRepository(supabase);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AuthProvider>.value(value: auth),
          Provider<ReturnRepository>.value(value: repository),
        ],
        child: MaterialApp(theme: buildAppTheme(), home: const ReturnScreen()),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.byKey(const ValueKey('return-location-field')), findsOneWidget);
    await tester.tap(find.byType(CheckboxListTile).first);
    await tester.pumpAndSettle();

    final listView = find.byType(ListView).first;
    await tester.drag(listView, const Offset(0, -400));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Submit Return'));
    await tester.pumpAndSettle();

    expect(find.text('Return location is required.'), findsOneWidget);
    expect(repository.submittedLocations, isEmpty);

    final locationField = find.byKey(const ValueKey('return-location-field'));
    await tester.ensureVisible(locationField);
    await tester.enterText(locationField, 'Main Warehouse');
    await tester.ensureVisible(find.text('Submit Return'));
    await tester.tap(find.text('Submit Return'));
    await tester.pumpAndSettle();

    expect(repository.submittedLocations, ['Main Warehouse']);
  });
}
