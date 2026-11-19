import '../../../core/errors/app_exception.dart';
import '../../../core/network/mobile_api_client.dart';
import '../models/borrow_request.dart';

/// Borrow-request operations against the Laravel API.
class BorrowRepository {
  BorrowRepository(this._api);

  final MobileApiClient _api;

  /// Refreshes borrow requests on an interval.
  ///
  /// Laravel exposes no websocket transport, so callers poll instead of
  /// subscribing to row changes. Returns a canceller.
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
          await listRequests();
          onChange();
        } catch (error) {
          onError(error);
        }
      },
    );
  }

  Future<List<BorrowRequest>> listRequests() async {
    final result = await _api.get('borrow-requests');

    return (result['requests'] as List? ?? const [])
        .map(
          (item) =>
              BorrowRequest.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList();
  }

  Future<BorrowRequest> getRequest(int id) async {
    final result = await _api.get('borrow-requests/$id');
    return _requestFrom(result);
  }

  Future<BorrowRequest> createRequest({
    required String idempotencyKey,
    required String borrowDate,
    required String dueDate,
    required String reason,
    required List<({int inventoryId, int quantity})> items,
  }) async {
    final result = await _api.post(
      'borrow-requests',
      body: {
        'idempotency_key': idempotencyKey,
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

    return _requestFrom(result);
  }

  Future<BorrowRequest> cancelRequest(int id) async {
    final result = await _api.post('borrow-requests/$id/cancel');
    return _requestFrom(result);
  }

  BorrowRequest _requestFrom(Map<String, dynamic> response) {
    final request = response['request'];

    if (request is! Map) {
      throw const AppException('The server returned an unexpected request.');
    }

    return BorrowRequest.fromJson(Map<String, dynamic>.from(request));
  }
}
