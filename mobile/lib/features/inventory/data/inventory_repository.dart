import 'dart:convert';

import 'package:supabase_flutter/supabase_flutter.dart';

import '../../../core/errors/supabase_call.dart';
import '../models/inventory_item.dart';

class InventoryRepository {
  InventoryRepository(this._supabase);

  final SupabaseClient _supabase;

  /// Resolves a raw QR payload or item code to current item details. The
  /// backend treats any scanned JSON as a hint and returns live availability.
  Future<InventoryItem> lookup(String code) => supabaseCall(() async {
    final json = await _supabase.rpc(
      'mobile_inventory_lookup',
      params: {'p_code': code},
    );
    return InventoryItem.fromJson(Map<String, dynamic>.from(json as Map));
  });

  Future<List<InventoryItem>> search(String query) => supabaseCall(() async {
    final json = await _supabase.rpc(
      'mobile_inventory_search',
      params: {'p_query': query.trim(), 'p_limit': 50},
    );
    return (json as List? ?? const [])
        .map(
          (item) =>
              InventoryItem.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList();
  });

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

    final uri = Uri.tryParse(trimmed);
    if (uri != null && uri.hasScheme && uri.pathSegments.isNotEmpty) {
      final token = uri.pathSegments.last.toUpperCase();
      if (RegExp(r'^QR-[A-Z0-9]{32}$').hasMatch(token)) return token;
    }

    return trimmed;
  }
}
