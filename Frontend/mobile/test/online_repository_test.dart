import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/core/errors/api_call.dart';
import 'package:rgv_employee_app/core/errors/app_exception.dart';
import 'package:rgv_employee_app/features/borrow/data/borrow_repository.dart';
import 'package:rgv_employee_app/features/dashboard/data/dashboard_repository.dart';
import 'package:rgv_employee_app/features/inventory/data/inventory_repository.dart';
import 'package:rgv_employee_app/features/return/data/return_repository.dart';
import 'support/fake_mobile_api.dart';

void main() {
  test('dashboard dismissal posts to the Laravel endpoint', () async {
    final api = FakeMobileApi();
    final repository = DashboardRepository(api);

    await repository.dismissRecentRequest(42);

    expect(api.paths, ['dashboard/recent-requests/42/dismiss']);
  });

  test(
    'return list accepts only borrowed requests and presents them as approved',
    () async {
      final api = FakeMobileApi()
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
    'borrow creation sends the API payload and parses the request',
    () async {
      final api = FakeMobileApi()
        ..response = {
          'request': {'id': 42, 'request_number': 'BR-42', 'status': 'pending'},
        };
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
      expect(api.bodies.single, containsPair('idempotency_key', 'borrow-42'));
      expect(api.bodies.single, containsPair('reason', 'Site work'));
    },
  );

  test('borrow history surfaces API failures instead of cached data', () async {
    final failure = AppException(
      'Could not reach the server.',
      isNetworkError: true,
    );
    final repository = BorrowRepository(FakeMobileApi()..failure = failure);

    await expectLater(repository.listRequests(), throwsA(same(failure)));
  });

  test(
    'return submission sends current-password-free return payload',
    () async {
      final api = FakeMobileApi()..response = {'request': null};
      final repository = ReturnRepository(api);

      await repository.submitReturn(
        requestId: 42,
        idempotencyKey: 'return-42',
        returnLocation: 'Main Warehouse',
        items: [(borrowItemId: 5, condition: 'good', notes: null)],
      );

      expect(api.paths, ['borrow-requests/42/return']);
      expect(
        api.bodies.single,
        containsPair('return_location', 'Main Warehouse'),
      );
    },
  );

  test('inventory scans require a current lookup', () async {
    final failure = AppException(
      'Could not reach the server.',
      isNetworkError: true,
    );
    final api = FakeMobileApi()..failure = failure;
    final repository = InventoryRepository(api);

    await expectLater(repository.lookup('QR-1'), throwsA(same(failure)));
    expect(api.paths, ['inventory/lookup']);
  });

  test('inventory browse calls the Laravel search endpoint', () async {
    final api = FakeMobileApi()
      ..response = {
        'items': [
          {
            'id': 9,
            'item_code': 'RGV-009',
            'name': 'Portable Generator',
            'quantity': 2,
            'status': 'available',
          },
        ],
      };
    final repository = InventoryRepository(api);

    final items = await repository.search('  generator  ');

    expect(api.paths, ['inventory/search']);
    expect(api.lastQuery, {'q': 'generator'});
    expect(items.single.name, 'Portable Generator');
  });

  group('apiCall error translation', () {
    test('marks socket failures as network errors', () async {
      await expectLater(
        apiCall(() async => throw const SocketException('offline')),
        throwsA(
          isA<AppException>().having(
            (e) => e.isNetworkError,
            'isNetworkError',
            isTrue,
          ),
        ),
      );
    });
  });
}
