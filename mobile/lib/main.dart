import 'package:flutter/material.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

import 'app.dart';
import 'core/config/app_config.dart';
import 'features/auth/data/auth_repository.dart';
import 'features/auth/providers/auth_provider.dart';
import 'features/borrow/data/borrow_repository.dart';
import 'features/dashboard/data/dashboard_repository.dart';
import 'features/inventory/data/inventory_repository.dart';
import 'features/return/data/return_repository.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  AppConfig.validate();
  await Supabase.initialize(
    url: AppConfig.supabaseUrl,
    publishableKey: AppConfig.supabaseAnonKey,
  );

  final supabase = Supabase.instance.client;
  final authRepository = AuthRepository(supabase);
  final authProvider = AuthProvider(repository: authRepository);

  runApp(
    RgvApp(
      authProvider: authProvider,
      authRepository: authRepository,
      dashboardRepository: DashboardRepository(supabase),
      inventoryRepository: InventoryRepository(supabase),
      borrowRepository: BorrowRepository(supabase),
      returnRepository: ReturnRepository(supabase),
    ),
  );
}
