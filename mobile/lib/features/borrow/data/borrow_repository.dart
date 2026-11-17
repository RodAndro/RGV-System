import 'package:supabase_flutter/supabase_flutter.dart';

import '../../../core/errors/app_exception.dart';
import '../../../core/network/mobile_api_client.dart';
import '../models/borrow_request.dart';

class BorrowRepository {
  BorrowRepository(this._api);

  final MobileApiClient _api;

  RealtimeChannel subscribeToRequestChanges({
    required int employeeId,
    required void Function() onChange,
    required void Function(Object?) onError,
  }) => _api.subscribeToBorrowRequestChanges(
    employeeId: employeeId,
    channelName: 'history',
    onChange: onChange,
    onError: onError,
  );

  Future<List<BorrowRequest>> listRequests() async {
    final result = await _api.get('borrow-requests');
    return (result['requests'] as List? ?? const [])
        .map(
          (item) =>
              BorrowRequest.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList();
  }

  Future<BorrowRequest> getRequest(int id) async =>
      (await listRequests()).firstWhere(
        (request) => request.id == id,
        orElse: () => throw const AppException('Borrow request not found.'),
      );

  Future<BorrowRequest> createRequest({
    required String idempotencyKey,
    required String borrowDate,
    required String dueDate,
    required String reason,
    required List<({int inventoryId, int quantity})> items,
  }) async {
    final result = await _api.post(
      'borrow-requests',
      idempotencyKey: idempotencyKey,
      body: {
        'borrow_date': borrowDate,
        'due_date': dueDate,
        'reason': reason,
        'items': items
            .map(
              (item) => {
                'inventory_id': item.inventoryId,
                'quantity': item.quantity,
              },
            )
            .toList(),
      },
    );
    return BorrowRequest.fromJson(
      Map<String, dynamic>.from(result['request'] as Map),
    );
  }

  Future<BorrowRequest> cancelRequest(int id) async {
    final result = await _api.post('borrow-requests/$id/cancel');
    return BorrowRequest.fromJson(
      Map<String, dynamic>.from(result['request'] as Map),
    );
  }
}
