import 'dart:convert';

import '../../../core/network/api_client.dart';
import '../models/inventory_item.dart';

class InventoryRepository {
  InventoryRepository(this._api);

  final ApiClient _api;

  /// Resolves a raw QR payload or item code to current item details. The
  /// backend treats any scanned JSON as a hint and returns live availability.
  Future<InventoryItem> lookup(String code) async {
    final json = await _api.get('/inventory/lookup', query: {'code': code});
    return InventoryItem.fromJson(json['item'] as Map<String, dynamic>);
  }

  /// Decodes a raw QR string into the best item-code hint (JSON or plain).
  String normalizeCode(String raw) {
    final trimmed = raw.trim();
    try {
      final decoded = jsonDecode(trimmed);
      if (decoded is Map<String, dynamic>) {
        for (final key in ['item_code', 'itemCode', 'code', 'id']) {
          final value = decoded[key];
          if (value is String && value.isNotEmpty) return value;
          if (value is num) return value.toString();
        }
      }
    } on FormatException {
      // Not JSON — treat as a plain item code.
    }
    return trimmed;
  }
}
