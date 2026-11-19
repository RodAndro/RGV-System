import 'package:flutter/material.dart';

import 'app.dart';
import 'core/config/app_config.dart';
import 'core/network/mobile_api_client.dart';
import 'features/auth/data/auth_repository.dart';
import 'features/auth/providers/auth_provider.dart';
import 'features/borrow/data/borrow_repository.dart';
import 'features/dashboard/data/dashboard_repository.dart';
import 'features/inventory/data/inventory_repository.dart';
import 'features/return/data/return_repository.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const _BootstrapApp());
}

/// Builds the dependency graph once and reports an invalid API address.
///
/// Startup performs no network I/O: the stored token is restored lazily by
/// AuthGate, so the app opens even when the server is unreachable and shows a
/// clear connection error instead of failing to start.
class _BootstrapApp extends StatefulWidget {
  const _BootstrapApp();

  @override
  State<_BootstrapApp> createState() => _BootstrapAppState();
}

class _BootstrapAppState extends State<_BootstrapApp> {
  late final _AppDependencies _dependencies = _initialize();

  _AppDependencies _initialize() {
    AppConfig.validate();

    final api = MobileApiClient();
    final authRepository = AuthRepository(api);
    final authProvider = AuthProvider(repository: authRepository);

    // A rejected token signs the employee out of the whole app.
    api.onUnauthenticated = authProvider.handleUnauthenticated;

    return _AppDependencies(
      authProvider: authProvider,
      authRepository: authRepository,
      dashboardRepository: DashboardRepository(api),
      inventoryRepository: InventoryRepository(api),
      borrowRepository: BorrowRepository(api),
      returnRepository: ReturnRepository(api),
    );
  }

  @override
  Widget build(BuildContext context) => _dependencies.buildApp();
}

class _AppDependencies {
  const _AppDependencies({
    required this.authProvider,
    required this.authRepository,
    required this.dashboardRepository,
    required this.inventoryRepository,
    required this.borrowRepository,
    required this.returnRepository,
  });

  final AuthProvider authProvider;
  final AuthRepository authRepository;
  final DashboardRepository dashboardRepository;
  final InventoryRepository inventoryRepository;
  final BorrowRepository borrowRepository;
  final ReturnRepository returnRepository;

  Widget buildApp() => RgvApp(
    authProvider: authProvider,
    authRepository: authRepository,
    dashboardRepository: dashboardRepository,
    inventoryRepository: inventoryRepository,
    borrowRepository: borrowRepository,
    returnRepository: returnRepository,
  );
}
