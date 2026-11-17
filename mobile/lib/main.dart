import 'package:flutter/material.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

import 'app.dart';
import 'core/config/app_config.dart';
import 'core/network/mobile_api_client.dart';
import 'core/theme/app_theme.dart';
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

/// Builds the dependency graph once and reports invalid Supabase configuration.
class _BootstrapApp extends StatefulWidget {
  const _BootstrapApp();

  @override
  State<_BootstrapApp> createState() => _BootstrapAppState();
}

class _BootstrapAppState extends State<_BootstrapApp> {
  late final Future<_AppDependencies> _dependencies = _initialize();

  Future<_AppDependencies> _initialize() async {
    AppConfig.validate();

    await Supabase.initialize(
      url: AppConfig.supabaseUrl,
      publishableKey: AppConfig.supabaseAnonKey,
    ).timeout(const Duration(seconds: 20));

    final supabase = Supabase.instance.client;
    final api = MobileApiClient(supabase: supabase);
    final authRepository = AuthRepository(supabase, api);

    return _AppDependencies(
      authProvider: AuthProvider(repository: authRepository),
      authRepository: authRepository,
      dashboardRepository: DashboardRepository(api),
      inventoryRepository: InventoryRepository(api),
      borrowRepository: BorrowRepository(api),
      returnRepository: ReturnRepository(api),
    );
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<_AppDependencies>(
      future: _dependencies,
      builder: (context, snapshot) {
        final data = snapshot.data;
        if (data != null) return data.buildApp();
        if (snapshot.hasError) return _StartupError(error: snapshot.error!);
        return MaterialApp(
          debugShowCheckedModeBanner: false,
          theme: buildAppTheme(),
          home: const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          ),
        );
      },
    );
  }
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

class _StartupError extends StatelessWidget {
  const _StartupError({required this.error});

  final Object error;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      theme: buildAppTheme(),
      home: Scaffold(
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Text(
              'Could not start the app\n\n$error',
              textAlign: TextAlign.center,
            ),
          ),
        ),
      ),
    );
  }
}
