import '../../../core/network/mobile_api_client.dart';
import '../models/dashboard_data.dart';

class DashboardRepository {
  DashboardRepository(this._api);

  final MobileApiClient _api;

  Future<DashboardData> fetch() async =>
      DashboardData.fromJson(await _api.get('dashboard'));

  Future<void> dismissRecentRequest(int requestId) async {
    await _api.post('dashboard/recent-requests/$requestId/dismiss');
  }
}
