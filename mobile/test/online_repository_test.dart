import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/core/errors/app_exception.dart';
import 'package:rgv_employee_app/core/network/mobile_api_client.dart';
import 'package:rgv_employee_app/features/borrow/data/borrow_repository.dart';
import 'package:rgv_employee_app/features/dashboard/data/dashboard_repository.dart';
import 'package:rgv_employee_app/features/inventory/data/inventory_repository.dart';
import 'package:rgv_employee_app/features/return/data/return_repository.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class _FakeApi extends MobileApiClient {
  _FakeApi()
    : super(
        supabase: SupabaseClient(
          'https://example.supabase.co',
          'test-publishable-key',
          authOptions: const AuthClientOptions(autoRefreshToken: false),
        ),
      );

  Object? failure;
  final List<String> paths = [];
  Map<String, dynamic> response = {};

  @override
  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, String>? query,
  }) async {
    paths.add(path);
    if (failure case final error?) throw error;
    return response;
  }

  @override
  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
    bool authenticate = true,
    String? idempotencyKey,
  }) async {
    paths.add(path);
    if (failure case final error?) throw error;
    response = {'path': path, 'body': body, 'idempotency_key': idempotencyKey};
    return {
      'request': {'id': 42, 'request_number': 'BR-42', 'status': 'pending'},
    };
  }
}

void main() {
  test(
    'dashboard dismissal is persisted through the Supabase RPC path',
    () async {
      final api = _FakeApi();
      final repository = DashboardRepository(api);

      await repository.dismissRecentRequest(42);

      expect(api.paths, ['dashboard/recent-requests/42/dismiss']);
    },
  );

  test(
    'return list accepts only borrowed requests and presents them as approved',
    () async {
      final api = _FakeApi()
        ..response = {
          'items': [
            {
              'borrow_item_id': 5,
              'request_number': 'BR-41',
              'request_id': 41,
              'status': 'approved',
              'quantity': 1,
            },
            {
              'borrow_item_id': 6,
              'request_number': 'BR-42',
              'request_id': 42,
              'status': 'borrowed',
              'quantity': 2,
            },
          ],
        };
      final repository = ReturnRepository(api);

      final items = await repository.returnableItems();

      expect(items, hasLength(1));
      expect(items.single.status, 'borrowed');
      expect(items.single.displayStatus, 'approved');
    },
  );

  test(
    'borrow creation returns only after Supabase accepts the request',
    () async {
      final api = _FakeApi();
      final repository = BorrowRepository(api);

      final request = await repository.createRequest(
        idempotencyKey: 'borrow-42',
        borrowDate: '2026-11-14',
        dueDate: '2026-11-21',
        reason: 'Site work',
        items: [(inventoryId: 9, quantity: 2)],
      );

      expect(request.id, 42);
      expect(api.paths, ['borrow-requests']);
    },
  );

  test(
    'borrow history surfaces Supabase failures instead of cached data',
    () async {
      final failure = AppException(
        'Could not reach Supabase.',
        isNetworkError: true,
      );
      final repository = BorrowRepository(_FakeApi()..failure = failure);

      await expectLater(repository.listRequests(), throwsA(same(failure)));
    },
  );

  test(
    'return submission surfaces Supabase failures without queuing',
    () async {
      final failure = AppException(
        'Could not reach Supabase.',
        isNetworkError: true,
      );
      final api = _FakeApi()..failure = failure;
      final repository = ReturnRepository(api);

      await expectLater(
        repository.submitReturn(
          requestId: 42,
          idempotencyKey: 'return-42',
          returnLocation: 'Main Warehouse',
          items: [(borrowItemId: 5, condition: 'good', notes: null)],
        ),
        throwsA(same(failure)),
      );
      expect(api.paths, ['borrow-requests/42/return']);
    },
  );

  test('inventory scans require a current Supabase lookup', () async {
    final failure = AppException(
      'Could not reach Supabase.',
      isNetworkError: true,
    );
    final api = _FakeApi()..failure = failure;
    final repository = InventoryRepository(api);

    await expectLater(repository.lookup('QR-1'), throwsA(same(failure)));
    expect(api.paths, ['inventory/lookup']);
  });
}
