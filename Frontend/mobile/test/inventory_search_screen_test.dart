import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:rgv_employee_app/features/borrow/data/borrow_repository.dart';
import 'package:rgv_employee_app/features/inventory/data/inventory_repository.dart';
import 'package:rgv_employee_app/features/inventory/models/inventory_item.dart';
import 'package:rgv_employee_app/features/inventory/screens/inventory_search_screen.dart';

import 'support/fake_mobile_api.dart';

class _StubInventoryRepository extends InventoryRepository {
  _StubInventoryRepository(this.items) : super(FakeMobileApi());

  final List<InventoryItem> items;
  final List<String> queries = [];

  @override
  Future<List<InventoryItem>> search(String query) async {
    queries.add(query);
    return items;
  }
}

void main() {
  testWidgets('inventory browser searches Laravel data and opens borrow form', (
    tester,
  ) async {
    final inventoryRepository = _StubInventoryRepository(const [
      InventoryItem(
        id: 9,
        itemCode: 'RGV-009',
        name: 'Portable Generator',
        quantity: 2,
      ),
    ]);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          Provider<InventoryRepository>.value(value: inventoryRepository),
          Provider<BorrowRepository>.value(
            value: BorrowRepository(FakeMobileApi()),
          ),
        ],
        child: const MaterialApp(home: InventorySearchScreen()),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Portable Generator'), findsOneWidget);
    expect(inventoryRepository.queries, ['']);

    await tester.enterText(find.byType(TextField), 'generator');
    await tester.pump(const Duration(milliseconds: 350));
    await tester.pumpAndSettle();
    expect(inventoryRepository.queries, ['', 'generator']);

    await tester.tap(find.text('Portable Generator'));
    await tester.pumpAndSettle();
    expect(find.text('Request to Borrow'), findsOneWidget);
  });
}
