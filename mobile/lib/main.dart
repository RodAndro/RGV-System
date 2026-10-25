import 'package:flutter/material.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

import 'app.dart';
import 'core/config/app_config.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/data/auth_repository.dart';
import 'features/auth/providers/auth_provider.dart';
import 'features/borrow/data/borrow_repository.dart';
import 'features/dashboard/data/dashboard_repository.dart';
import 'features/inventory/data/inventory_repository.dart';
import 'features/return/data/return_repository.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const _BootstrapApp());
}

class _BootstrapApp extends StatefulWidget {
  const _BootstrapApp();

  @override
  State<_BootstrapApp> createState() => _BootstrapAppState();
}

class _BootstrapAppState extends State<_BootstrapApp> {
  late final Future<_AppDependencies> _initialization = _initializeApp();

  Future<_AppDependencies> _initializeApp() async {
    AppConfig.validate();
    await Supabase.initialize(
      url: AppConfig.supabaseUrl,
      publishableKey: AppConfig.supabaseAnonKey,
    ).timeout(const Duration(seconds: 20));

    final supabase = Supabase.instance.client;
    final authRepository = AuthRepository(supabase);
    return _AppDependencies(
      authRepository: authRepository,
      authProvider: AuthProvider(repository: authRepository),
      dashboardRepository: DashboardRepository(supabase),
      inventoryRepository: InventoryRepository(supabase),
      borrowRepository: BorrowRepository(supabase),
      returnRepository: ReturnRepository(supabase),
    );
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<_AppDependencies>(
      future: _initialization,
      builder: (context, snapshot) {
        if (snapshot.hasError) {
          return _InitializationErrorApp(error: snapshot.error!);
        }
        if (!snapshot.hasData) {
          return MaterialApp(
            debugShowCheckedModeBanner: false,
            theme: buildAppTheme(),
            home: const Scaffold(
              body: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(),
                    SizedBox(height: 16),
                    Text('Starting employee app...'),
                  ],
                ),
              ),
            ),
          );
        }

        final dependencies = snapshot.requireData;
        return RgvApp(
          authProvider: dependencies.authProvider,
          authRepository: dependencies.authRepository,
          dashboardRepository: dependencies.dashboardRepository,
          inventoryRepository: dependencies.inventoryRepository,
          borrowRepository: dependencies.borrowRepository,
          returnRepository: dependencies.returnRepository,
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
}

class _InitializationErrorApp extends StatelessWidget {
  const _InitializationErrorApp({required this.error});

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
