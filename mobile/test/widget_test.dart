import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:rgv_employee_app/core/network/mobile_api_client.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/features/auth/data/auth_repository.dart';
import 'package:rgv_employee_app/features/auth/providers/auth_provider.dart';
import 'package:rgv_employee_app/features/auth/screens/login_screen.dart';
import 'package:rgv_employee_app/features/inventory/models/inventory_item.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

void main() {
  Widget loginApp() {
    final supabase = SupabaseClient(
      'https://example.supabase.co',
      'test-publishable-key',
      authOptions: const AuthClientOptions(autoRefreshToken: false),
    );
    final api = MobileApiClient(supabase: supabase);
    final auth = AuthRepository(supabase, api);
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider(repository: auth)),
      ],
      child: MaterialApp(theme: buildAppTheme(), home: const LoginScreen()),
    );
  }

  test(
    'inventory availability requires active status and positive quantity',
    () {
      InventoryItem item({
        required int quantity,
        String status = 'available',
        bool active = true,
      }) => InventoryItem(
        id: 1,
        itemCode: 'RGV-001',
        name: 'Test item',
        quantity: quantity,
        status: status,
        isActive: active,
      );

      expect(item(quantity: 1).isAvailable, isTrue);
      expect(item(quantity: 0).isAvailable, isFalse);
      expect(item(quantity: 5, status: 'maintenance').isAvailable, isFalse);
      expect(item(quantity: 5, active: false).isAvailable, isFalse);
    },
  );

  testWidgets('login screen renders email, password and sign-in button', (
    tester,
  ) async {
    await tester.pumpWidget(loginApp());

    expect(find.text('RGV Multi-Tech Services'), findsOneWidget);
    final logo = find.byKey(const ValueKey('login-logo'));
    expect(tester.getSize(logo), const Size(160, 160));
    expect(
      find.descendant(of: logo, matching: find.byType(ClipOval)),
      findsOneWidget,
    );
    final image = tester.widget<Image>(find.byType(Image).first);
    expect(image.width, 148);
    expect(image.height, 148);
    expect(image.fit, BoxFit.contain);
    expect(find.byType(TextFormField), findsNWidgets(2));
    expect(find.widgetWithText(ElevatedButton, 'Sign In'), findsOneWidget);
  });

  testWidgets('login screen validates empty fields', (tester) async {
    await tester.pumpWidget(loginApp());

    await tester.tap(find.widgetWithText(ElevatedButton, 'Sign In'));
    await tester.pump();

    expect(find.text('Enter your email address.'), findsOneWidget);
    expect(find.text('Enter your password.'), findsOneWidget);
  });

  testWidgets('password visibility toggle appears only for non-empty input', (
    tester,
  ) async {
    await tester.pumpWidget(loginApp());

    final passwordField = find.byType(TextFormField).last;
    expect(find.byIcon(Icons.visibility_off), findsNothing);
    expect(find.byIcon(Icons.visibility), findsNothing);

    await tester.enterText(passwordField, 'secret');
    await tester.pump();
    expect(find.byIcon(Icons.visibility_off), findsOneWidget);

    await tester.tap(find.byIcon(Icons.visibility_off));
    await tester.pump();
    expect(find.byIcon(Icons.visibility), findsOneWidget);

    await tester.enterText(passwordField, '');
    await tester.pump();
    expect(find.byIcon(Icons.visibility_off), findsNothing);
    expect(find.byIcon(Icons.visibility), findsNothing);
  });

  testWidgets('login screen does not advertise an offline transaction mode', (
    tester,
  ) async {
    await tester.pumpWidget(loginApp());

    expect(find.textContaining('Offline mode'), findsNothing);
    expect(find.textContaining('No connection. Signing in'), findsNothing);
    expect(find.byType(TextFormField), findsNWidgets(2));
  });
}
