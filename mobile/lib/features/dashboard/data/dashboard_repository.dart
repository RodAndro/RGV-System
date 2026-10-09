import 'package:supabase_flutter/supabase_flutter.dart';

import '../../../core/errors/supabase_call.dart';
import '../models/dashboard_data.dart';

class DashboardRepository {
  DashboardRepository(this._supabase);

  final SupabaseClient _supabase;

  Future<DashboardData> fetch() => supabaseCall(() async {
    final json = await _supabase.rpc('mobile_dashboard');
    return DashboardData.fromJson(Map<String, dynamic>.from(json as Map));
  });
}
