import '../../../core/network/api_client.dart';
import '../models/dashboard_data.dart';

class DashboardRepository {
  DashboardRepository(this._api);

  final ApiClient _api;

  Future<DashboardData> fetch() async {
    final json = await _api.get('/dashboard');
    return DashboardData.fromJson(json);
  }
}
