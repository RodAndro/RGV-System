import 'dart:convert';

import '../../../core/network/mobile_api_client.dart';
import '../models/inventory_item.dart';

class InventoryRepository {
  InventoryRepository(this._api);

  final MobileApiClient _api;

  Future<InventoryItem> lookup(String code) async {
    final result = await _api.get('inventory/lookup', query: {'code': code});
    return InventoryItem.fromJson(
      Map<String, dynamic>.from(result['item'] as Map),
    );
  }

  Future<List<InventoryItem>> search(String query) async {
    final result = await _api.get(
      'inventory/search',
      query: {'q': query.trim()},
    );
    return (result['items'] as List? ?? const [])
        .map(
          (item) =>
              InventoryItem.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList();
  }

  String normalizeCode(String raw) {
    final trimmed = raw.trim();
    try {
      final decoded = jsonDecode(trimmed);
      if (decoded is Map<String, dynamic>) {
        for (final key in [
          'qr_token',
          'qrToken',
          'token',
          'id',
          'item_code',
          'itemCode',
          'code',
        ]) {
          final value = decoded[key];
          if (value is String && value.isNotEmpty) return value;
          if (value is num) return value.toString();
        }
      }
    } on FormatException {
      // The QR content is a plain inventory code.
    }
    final uri = Uri.tryParse(trimmed);
    if (uri != null && uri.hasScheme && uri.pathSegments.isNotEmpty) {
      final token = uri.pathSegments.last.toUpperCase();
      if (RegExp(r'^QR-[A-Z0-9]{32}$').hasMatch(token)) return token;
    }
    return trimmed;
  }
}
