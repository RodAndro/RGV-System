import 'package:supabase_flutter/supabase_flutter.dart';

import '../config/app_config.dart';
import '../errors/app_exception.dart';
import '../errors/supabase_call.dart';

class MobileApiClient {
  MobileApiClient({required this.supabase});

  final SupabaseClient supabase;

  String get baseUrl => AppConfig.supabaseUrl;

  RealtimeChannel subscribeToBorrowRequestChanges({
    required int employeeId,
    required String channelName,
    required void Function() onChange,
    required void Function(Object?) onError,
  }) => supabase
      .channel('$channelName:borrow-requests:$employeeId')
      .onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'borrow_requests',
        filter: PostgresChangeFilter(
          type: PostgresChangeFilterType.eq,
          column: 'employee_id',
          value: employeeId,
        ),
        callback: (_) => onChange(),
      )
      .subscribe((status, error) {
        if (status == RealtimeSubscribeStatus.channelError ||
            status == RealtimeSubscribeStatus.timedOut) {
          onError(error);
        }
      });

  Future<Map<String, dynamic>> get(String path, {Map<String, String>? query}) =>
      _send('GET', path, query: query);

  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
    bool authenticate = true,
    String? idempotencyKey,
  }) => _send('POST', path, body: body, idempotencyKey: idempotencyKey);

  Future<Map<String, dynamic>> patch(
    String path, {
    Map<String, dynamic>? body,
  }) => _send('PATCH', path, body: body);

  Future<Map<String, dynamic>> put(String path, {Map<String, dynamic>? body}) =>
      _send('PUT', path, body: body);

  Future<Map<String, dynamic>> _send(
    String method,
    String path, {
    Map<String, dynamic>? body,
    Map<String, String>? query,
    String? idempotencyKey,
  }) async {
    final call = _rpcFor(
      method,
      path,
      body ?? const {},
      query ?? const {},
      idempotencyKey,
    );
    if (call == null) {
      throw const AppException('This Supabase operation is not supported.');
    }

    final result = await supabaseCall(
      () => supabase.rpc(call.rpc, params: call.params),
    );
    if (call.responseKey == null) return _asMap(result);
    if (result is List) {
      return {call.responseKey!: result};
    }
    return {call.responseKey!: _asMap(result)};
  }

  _RpcCall? _rpcFor(
    String method,
    String path,
    Map<String, dynamic> body,
    Map<String, String> query,
    String? idempotencyKey,
  ) {
    if (method == 'GET') {
      switch (path) {
        case 'me':
          return _RpcCall('mobile_employee_profile', {}, responseKey: 'user');
        case 'dashboard':
          return _RpcCall('mobile_dashboard', {});
        case 'inventory/lookup':
          return _RpcCall('mobile_inventory_lookup', {
            'p_code': query['code'] ?? '',
          }, responseKey: 'item');
        case 'inventory/search':
          return _RpcCall('mobile_inventory_search', {
            'p_query': query['q'] ?? '',
            'p_limit': int.tryParse(query['limit'] ?? '') ?? 50,
          }, responseKey: 'items');
        case 'borrow-requests':
          return _RpcCall(
            'mobile_list_borrow_requests',
            {},
            responseKey: 'requests',
          );
        case 'borrow-requests/returnable':
          return _RpcCall('mobile_returnable_items', {}, responseKey: 'items');
      }
    }

    if (method == 'POST') {
      if (path == 'borrow-requests') {
        return _RpcCall('mobile_create_borrow_request', {
          'p_borrow_date': body['borrow_date'],
          'p_due_date': body['due_date'],
          'p_reason': body['reason'],
          'p_items': body['items'],
          'p_idempotency_key': idempotencyKey ?? body['idempotency_key'],
        }, responseKey: 'request');
      }
      final dismissRecentRequest = RegExp(
        r'^dashboard/recent-requests/(\d+)/dismiss$',
      ).firstMatch(path);
      if (dismissRecentRequest != null) {
        return _RpcCall('mobile_dismiss_recent_request', {
          'p_request_id': int.parse(dismissRecentRequest.group(1)!),
        });
      }
      final cancel = RegExp(r'^borrow-requests/(\d+)/cancel$').firstMatch(path);
      if (cancel != null) {
        return _RpcCall('mobile_cancel_borrow_request', {
          'p_request_id': int.parse(cancel.group(1)!),
        }, responseKey: 'request');
      }
      final returnRequest = RegExp(
        r'^borrow-requests/(\d+)/return$',
      ).firstMatch(path);
      if (returnRequest != null) {
        return _RpcCall('mobile_return_borrow_items', {
          'p_request_id': int.parse(returnRequest.group(1)!),
          'p_idempotency_key': idempotencyKey ?? body['idempotency_key'],
          'p_return_location': body['return_location'],
          'p_items': body['items'],
        }, responseKey: 'request');
      }
    }

    if (method == 'PATCH' && path == 'profile') {
      return _RpcCall('mobile_update_employee_name', {
        'p_name': body['name'],
      }, responseKey: 'user');
    }
    return null;
  }

  Map<String, dynamic> _asMap(Object? value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) return Map<String, dynamic>.from(value);
    throw const AppException('Supabase returned an invalid response.');
  }
}

class _RpcCall {
  const _RpcCall(this.rpc, this.params, {this.responseKey});

  final String rpc;
  final Map<String, dynamic> params;
  final String? responseKey;
}
