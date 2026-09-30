import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/features/auth/screens/login_screen.dart';

void main() {
  testWidgets('login screen renders email, password and sign-in button',
      (tester) async {
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
}
