import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/features/auth/screens/login_screen.dart';
import 'package:rgv_employee_app/features/inventory/models/inventory_item.dart';
import 'package:rgv_employee_app/main.dart' as app;

void main() {
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
    await tester.pumpWidget(
      MaterialApp(theme: buildAppTheme(), home: const LoginScreen()),
    );

    expect(find.text('RGV Multi-Tech Services'), findsOneWidget);
    expect(find.byType(TextFormField), findsNWidgets(2));
    expect(find.widgetWithText(ElevatedButton, 'Sign In'), findsOneWidget);
  });

  testWidgets('login screen validates empty fields', (tester) async {
    await tester.pumpWidget(
      MaterialApp(theme: buildAppTheme(), home: const LoginScreen()),
    );

    await tester.tap(find.widgetWithText(ElevatedButton, 'Sign In'));
    await tester.pump();

    expect(find.text('Enter your email address.'), findsOneWidget);
    expect(find.text('Enter your password.'), findsOneWidget);
  });

  testWidgets('startup shows a configuration error instead of a blank screen', (
    tester,
  ) async {
    await app.main();
    await tester.pumpAndSettle();

    expect(find.textContaining('Could not start the app'), findsOneWidget);
    expect(
      find.textContaining('SUPABASE_URL and SUPABASE_ANON_KEY'),
      findsOneWidget,
    );
  });
}
