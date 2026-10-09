import 'package:supabase_flutter/supabase_flutter.dart';

import '../../../core/errors/supabase_call.dart';
import '../../borrow/models/borrow_request.dart';
import '../models/returnable_item.dart';

class ReturnRepository {
  ReturnRepository(this._supabase);

  final SupabaseClient _supabase;

  Future<List<ReturnableItem>> returnableItems() => supabaseCall(() async {
    final json = await _supabase.rpc('mobile_returnable_items');
    return (json as List? ?? const [])
        .map(
          (e) => ReturnableItem.fromJson(Map<String, dynamic>.from(e as Map)),
        )
        .toList();
  });

  /// Marks the selected items returned.
  ///
  /// [idempotencyKey] must be stable across retries of the same submission so
  /// the server can deduplicate the upload and never restore stock twice.
  Future<BorrowRequest> submitReturn({
    required int requestId,
    required String idempotencyKey,
    required String returnLocation,
    required List<({int borrowItemId, String condition, String? notes})> items,
  }) => supabaseCall(() async {
    final json = await _supabase.rpc(
      'mobile_return_borrow_items',
      params: {
        'p_request_id': requestId,
        'p_idempotency_key': idempotencyKey,
        'p_return_location': returnLocation,
        'p_items': items
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
    return BorrowRequest.fromJson(Map<String, dynamic>.from(json as Map));
  });
}
