import '../../../core/network/api_client.dart';
import '../models/borrow_request.dart';

class BorrowRepository {
  BorrowRepository(this._api);

  final ApiClient _api;

  Future<List<BorrowRequest>> listRequests() async {
    final json = await _api.get('/borrow-requests');
    return (json['requests'] as List? ?? const [])
        .map((e) => BorrowRequest.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<BorrowRequest> getRequest(int id) async {
    final json = await _api.get('/borrow-requests/$id');
    return BorrowRequest.fromJson(json['request'] as Map<String, dynamic>);
  }

  Future<BorrowRequest> createRequest({
    required String borrowDate,
    required String dueDate,
    required String reason,
    required List<({int inventoryId, int quantity})> items,
  }) async {
    final json = await _api.post('/borrow-requests', body: {
      'borrow_date': borrowDate,
      'due_date': dueDate,
      'reason': reason,
      'items': items
          .map((e) => {'inventory_id': e.inventoryId, 'quantity': e.quantity})
          .toList(),
    });
    return BorrowRequest.fromJson(json['request'] as Map<String, dynamic>);
  }

  Future<BorrowRequest> cancelRequest(int id) async {
    final json = await _api.post('/borrow-requests/$id/cancel');
    return BorrowRequest.fromJson(json['request'] as Map<String, dynamic>);
  }
}
