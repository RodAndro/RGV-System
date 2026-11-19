import 'package:rgv_employee_app/core/network/mobile_api_client.dart';

/// Test double for [MobileApiClient].
///
/// Records the paths and bodies it was asked to send and returns canned
/// responses, so repositories and screens can be tested without a server.
class FakeMobileApi extends MobileApiClient {
  FakeMobileApi({super.baseUrl = 'http://127.0.0.1:8000/api/v1/mobile'});

  Object? failure;
  Map<String, dynamic> response = {};

  final List<String> paths = [];
  final List<Map<String, dynamic>?> bodies = [];
  Map<String, String>? lastQuery;

  @override
  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, String>? query,
    bool authenticate = true,
  }) async {
    paths.add(path);
    lastQuery = query;
    bodies.add(null);
    if (failure case final error?) throw error;
    return response;
  }

  @override
  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
    bool authenticate = true,
  }) async {
    paths.add(path);
    bodies.add(body);
    if (failure case final error?) throw error;
    return response;
  }

  @override
  Future<Map<String, dynamic>> patch(
    String path, {
    Map<String, dynamic>? body,
    bool authenticate = true,
  }) async {
    paths.add(path);
    bodies.add(body);
    if (failure case final error?) throw error;
    return response;
  }

  @override
  Future<Map<String, dynamic>> put(
    String path, {
    Map<String, dynamic>? body,
    bool authenticate = true,
  }) async {
    paths.add(path);
    bodies.add(body);
    if (failure case final error?) throw error;
    return response;
  }

  @override
  void setToken(String? token) {}
}
