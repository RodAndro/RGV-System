import 'package:supabase_flutter/supabase_flutter.dart';

import '../../../core/errors/supabase_call.dart';
import '../models/borrow_request.dart';

class BorrowRepository {
  BorrowRepository(this._supabase);

  final SupabaseClient _supabase;

  Future<List<BorrowRequest>> listRequests() => supabaseCall(() async {
    final json = await _supabase.rpc('mobile_list_borrow_requests');
    return (json as List? ?? const [])
        .map((e) => BorrowRequest.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
  });

  Future<BorrowRequest> getRequest(int id) async =>
      (await listRequests()).firstWhere((request) => request.id == id);

  Future<BorrowRequest> createRequest({
    required String borrowDate,
    required String dueDate,
    required String reason,
    required List<({int inventoryId, int quantity})> items,
  }) => supabaseCall(() async {
    final json = await _supabase.rpc(
      'mobile_create_borrow_request',
      params: {
        'p_borrow_date': borrowDate,
        'p_due_date': dueDate,
        'p_reason': reason,
        'p_items': items
            .map(
              (item) => {
                'inventory_id': item.inventoryId,
                'quantity': item.quantity,
              },
            )
            .toList(),
      },
    );
    return BorrowRequest.fromJson(Map<String, dynamic>.from(json as Map));
  });

  Future<BorrowRequest> cancelRequest(int id) => supabaseCall(() async {
    final json = await _supabase.rpc(
      'mobile_cancel_borrow_request',
      params: {'p_request_id': id},
    );
    return BorrowRequest.fromJson(Map<String, dynamic>.from(json as Map));
  });
}
