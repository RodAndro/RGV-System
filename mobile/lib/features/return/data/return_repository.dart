import 'package:path/path.dart' as p;

import '../../../core/network/api_client.dart';
import '../../borrow/models/borrow_request.dart';
import '../models/returnable_item.dart';

class ReturnRepository {
  ReturnRepository(this._api);

  final ApiClient _api;

  Future<List<ReturnableItem>> returnableItems() async {
    final json = await _api.get('/borrow-requests/returnable');
    return (json['items'] as List? ?? const [])
        .map((e) => ReturnableItem.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  /// Uploads a return-proof photo and marks the selected items returned.
  ///
  /// [idempotencyKey] must be stable across retries of the same submission so
  /// the server can deduplicate the upload and never restore stock twice.
  Future<BorrowRequest> submitReturn({
    required int requestId,
    required String idempotencyKey,
    required String photoPath,
    required List<({int borrowItemId, String condition, String? notes})> items,
  }) async {
    final fields = <String, String>{
      'idempotency_key': idempotencyKey,
    };
    for (var i = 0; i < items.length; i++) {
      final item = items[i];
      fields['items[$i][borrow_item_id]'] = item.borrowItemId.toString();
      fields['items[$i][condition_returned]'] = item.condition;
      fields['items[$i][damage_notes]'] = item.notes ?? '';
    }

    final json = await _api.postMultipart(
      '/borrow-requests/$requestId/return',
      fields: fields,
      files: {'photo': photoPath},
    );
    return BorrowRequest.fromJson(json['request'] as Map<String, dynamic>);
  }

  /// Derives a fallback MIME type from the file extension (Laravel validates
  /// the `photo` field as an image).
  String mimeTypeFor(String filePath) {
    switch (p.extension(filePath).toLowerCase()) {
      case '.png':
        return 'image/png';
      case '.webp':
        return 'image/webp';
      case '.heic':
      case '.heif':
        return 'image/heic';
      case '.jpg':
      case '.jpeg':
      default:
        return 'image/jpeg';
    }
  }
}
