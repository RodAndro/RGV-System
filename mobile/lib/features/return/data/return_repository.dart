import 'package:supabase_flutter/supabase_flutter.dart';

import '../../../core/network/mobile_api_client.dart';
import '../../borrow/models/borrow_request.dart';
import '../models/returnable_item.dart';

class ReturnRepository {
  ReturnRepository(this._api);

  final MobileApiClient _api;

  RealtimeChannel subscribeToRequestChanges({
    required int employeeId,
    required void Function() onChange,
    required void Function(Object?) onError,
  }) => _api.subscribeToBorrowRequestChanges(
    employeeId: employeeId,
    channelName: 'return',
    onChange: onChange,
    onError: onError,
  );

  Future<List<ReturnableItem>> returnableItems() async {
    final result = await _api.get('borrow-requests/returnable');
    final items = (result['items'] as List? ?? const [])
        .map(
          (item) =>
              ReturnableItem.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList();
    return items.where((item) => item.status == 'borrowed').toList();
  }

  Future<BorrowRequest> submitReturn({
    required int requestId,
    required String idempotencyKey,
    required String returnLocation,
    required List<({int borrowItemId, String condition, String? notes})> items,
  }) async {
    final result = await _api.post(
      'borrow-requests/$requestId/return',
      idempotencyKey: idempotencyKey,
      body: {
        'idempotency_key': idempotencyKey,
        'return_location': returnLocation,
        'items': items
            .map(
              (item) => {
                'borrow_item_id': item.borrowItemId,
                'condition_returned': item.condition,
                'damage_notes': item.notes,
              },
            )
            .toList(),
      },
    );
    return BorrowRequest.fromJson(
      Map<String, dynamic>.from(result['request'] as Map),
    );
  }
}
