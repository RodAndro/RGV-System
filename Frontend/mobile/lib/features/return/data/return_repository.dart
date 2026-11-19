import '../../../core/network/mobile_api_client.dart';
import '../models/returnable_item.dart';

/// Returnable items and return submission against the Laravel API.
class ReturnRepository {
  ReturnRepository(this._api);

  final MobileApiClient _api;

  /// Refreshes returnable items on an interval instead of subscribing to
  /// row changes, since Laravel exposes no websocket transport.
  ApiPoller subscribeToRequestChanges({
    required int employeeId,
    required void Function() onChange,
    required void Function(Object?) onError,
    Duration interval = const Duration(seconds: 20),
  }) {
    return ApiPoller.periodic(
      interval: interval,
      onTick: () async {
        try {
          await returnableItems();
          onChange();
        } catch (error) {
          onError(error);
        }
      },
    );
  }

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

  Future<Map<String, dynamic>> submitReturn({
    required int requestId,
    required String idempotencyKey,
    required String returnLocation,
    required List<({int borrowItemId, String condition, String? notes})> items,
  }) async {
    final result = await _api.post(
      'borrow-requests/$requestId/return',
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

    return result;
  }
}
