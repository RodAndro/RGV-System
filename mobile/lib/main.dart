import 'package:flutter/material.dart';

import 'app.dart';
import 'core/config/app_config.dart';
import 'core/network/api_client.dart';
import 'core/storage/token_storage.dart';
import 'features/auth/data/auth_repository.dart';
import 'features/auth/providers/auth_provider.dart';
import 'features/borrow/data/borrow_repository.dart';
import 'features/dashboard/data/dashboard_repository.dart';
import 'features/inventory/data/inventory_repository.dart';
import 'features/return/data/return_repository.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  AppConfig.assertHttpsInProduction();

  final storage = TokenStorage();
  final api = ApiClient(baseUrl: AppConfig.apiBaseUrl);
  final authRepository = AuthRepository(api);
  final authProvider = AuthProvider(storage: storage, repository: authRepository);
  api.tokenProvider = authProvider.tokenProvider;

  runApp(RgvApp(
    authProvider: authProvider,
    authRepository: authRepository,
    dashboardRepository: DashboardRepository(api),
    inventoryRepository: InventoryRepository(api),
    borrowRepository: BorrowRepository(api),
    returnRepository: ReturnRepository(api),
  ));
}
