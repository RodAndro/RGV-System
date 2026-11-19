import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/shared/widgets/review_dialog.dart';

void main() {
  testWidgets('review dialog shows a details table and confirms submission', (
    tester,
  ) async {
    bool? result;
    await tester.pumpWidget(
      MaterialApp(
        theme: buildAppTheme(),
        home: Builder(
          builder: (context) => Scaffold(
            body: TextButton(
              onPressed: () async {
                result = await showDialog<bool>(
                  context: context,
                  builder: (_) => const ReviewDialog(
                    title: 'Review request',
                    subtitle: 'Please verify before submitting.',
                    rows: [
                      ReviewDialogRow(
                        label: 'Borrow date',
                        value: 'Nov 14, 2026',
                      ),
                      ReviewDialogRow(label: 'Reason', value: 'Repair work'),
                    ],
                    cancelLabel: 'Edit',
                    confirmLabel: 'Submit',
                  ),
                );
              },
              child: const Text('Open review'),
            ),
          ),
        ),
      ),
    );

    await tester.tap(find.text('Open review'));
    await tester.pumpAndSettle();

    expect(find.text('Request detail'), findsOneWidget);
    expect(find.text('Provided information'), findsOneWidget);
    expect(find.text('Nov 14, 2026'), findsOneWidget);
    await tester.tap(find.text('Submit'));
    await tester.pumpAndSettle();

    expect(result, isTrue);
  });

  testWidgets('review dialog close button cancels without confirming', (
    tester,
  ) async {
    bool? result;
    await tester.pumpWidget(
      MaterialApp(
        theme: buildAppTheme(),
        home: Builder(
          builder: (context) => Scaffold(
            body: TextButton(
              onPressed: () async {
                result = await showDialog<bool>(
                  context: context,
                  builder: (_) => const ReviewDialog(
                    title: 'Confirm action',
                    rows: [],
                    confirmLabel: 'Continue',
                  ),
                );
              },
              child: const Text('Open review'),
            ),
          ),
        ),
      ),
    );

    await tester.tap(find.text('Open review'));
    await tester.pumpAndSettle();
    await tester.tap(find.byTooltip('Close'));
    await tester.pumpAndSettle();

    expect(result, isFalse);
  });

  testWidgets('dialog can omit the close button', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: buildAppTheme(),
        home: const Scaffold(
          body: ReviewDialog(
            title: 'Sign out?',
            rows: [],
            confirmLabel: 'Sign out',
            showCloseButton: false,
          ),
        ),
      ),
    );

    expect(find.byTooltip('Close'), findsNothing);
  });
}
