import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/theme/app_theme.dart';
import 'features/auth/data/auth_repository.dart';
import 'features/auth/providers/auth_provider.dart';
import 'features/auth/screens/login_screen.dart';
import 'features/auth/screens/mfa_screen.dart';
import 'features/borrow/data/borrow_repository.dart';
import 'features/dashboard/data/dashboard_repository.dart';
import 'features/dashboard/screens/dashboard_screen.dart';
import 'features/inventory/data/inventory_repository.dart';
import 'features/return/data/return_repository.dart';

class RgvApp extends StatelessWidget {
  const RgvApp({
    super.key,
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

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
        Provider<AuthRepository>.value(value: authRepository),
        Provider<DashboardRepository>.value(value: dashboardRepository),
        Provider<InventoryRepository>.value(value: inventoryRepository),
        Provider<BorrowRepository>.value(value: borrowRepository),
        Provider<ReturnRepository>.value(value: returnRepository),
      ],
      child: MaterialApp(
        title: 'RGV Employee',
        debugShowCheckedModeBanner: false,
        theme: buildAppTheme(),
        home: const AuthGate(),
      ),
    );
  }
}

/// Decides which top-level screen to show based on the session state.
class AuthGate extends StatefulWidget {
  const AuthGate({super.key});

  @override
  State<AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<AuthGate> {
  @override
  void initState() {
    super.initState();
    // Restore any persisted session on startup.
    context.read<AuthProvider>().restoreSession();
  }

  @override
  Widget build(BuildContext context) {
    final status = context.watch<AuthProvider>().status;
    switch (status) {
      case AuthStatus.initializing:
        return const Scaffold(
          body: Center(child: CircularProgressIndicator()),
        );
      case AuthStatus.unauthenticated:
        return const LoginScreen();
      case AuthStatus.mfaPending:
        return const MfaScreen();
      case AuthStatus.authenticated:
        return const DashboardScreen();
    }
  }
}
