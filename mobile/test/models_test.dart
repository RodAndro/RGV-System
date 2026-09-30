import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/features/auth/models/user.dart';
import 'package:rgv_employee_app/features/borrow/models/borrow_request.dart';
import 'package:rgv_employee_app/features/dashboard/models/dashboard_data.dart';
import 'package:rgv_employee_app/features/inventory/models/inventory_item.dart';
import 'package:rgv_employee_app/features/return/models/returnable_item.dart';

void main() {
  group('model parsing (snake_case API contract)', () {
    test('User.fromJson parses login payload', () {
      final user = User.fromJson(const {
        'id': 2,
        'name': 'Juan Dela Cruz',
        'email': 'juan.delacruz@rgvtech.com',
        'phone': '0917',
        'avatar_path': 'avatars/juan.png',
        'is_active': true,
        'mfa_enabled': false,
        'role': 'employee',
      });

      expect(user.id, 2);
      expect(user.name, 'Juan Dela Cruz');
      expect(user.email, 'juan.delacruz@rgvtech.com');
      expect(user.role, 'employee');
    });

    test('InventoryItem.fromJson parses lookup payload', () {
      final item = InventoryItem.fromJson(const {
        'id': 9,
        'item_code': 'RGV-009',
        'name': 'Handheld Jackhammer',
        'brand': 'INGCO',
        'category': 'Tools',
        'quantity': 1,
        'unit': 'unit',
        'status': 'available',
        'condition': 'good',
        'is_active': true,
      });

      expect(item.itemCode, 'RGV-009');
      expect(item.brand, 'INGCO');
      expect(item.quantity, 1);
      expect(item.isAvailable, isTrue);
    });

    test('BorrowRequest.fromJson parses request with items', () {
      final request = BorrowRequest.fromJson(const {
        'id': 5,
        'request_number': 'BR-ABC123',
        'status': 'pending',
        'borrow_date': '2026-09-30',
        'due_date': '2026-10-07',
        'items': [
          {
            'id': 11,
            'inventory_id': 9,
            'item_code': 'RGV-009',
            'name': 'Handheld Jackhammer',
            'quantity': 1,
            'is_returned': false,
          }
        ],
      });

      expect(request.requestNumber, 'BR-ABC123');
      expect(request.status, 'pending');
      expect(request.items, hasLength(1));
      expect(request.items.first.name, 'Handheld Jackhammer');
    });

    test('BorrowSummary.fromJson parses dashboard summary', () {
      final summary = BorrowSummary.fromJson(const {
        'total': 10,
        'pending': 2,
        'approved': 3,
        'borrowed': 4,
        'returned': 1,
        'overdue': 1,
      });

      expect(summary.total, 10);
      expect(summary.pending, 2);
      expect(summary.overdue, 1);
    });

    test('ReturnableItem.fromJson parses returnable payload', () {
      final item = ReturnableItem.fromJson(const {
        'borrow_item_id': 21,
        'request_number': 'BR-ABC123',
        'request_id': 5,
        'borrow_date': '2026-09-30',
        'due_date': '2026-10-07',
        'quantity': 1,
        'condition_borrowed': 'good',
        'inventory': {
          'id': 9,
          'item_code': 'RGV-009',
          'name': 'Handheld Jackhammer',
          'brand': 'INGCO',
          'unit': 'unit',
        },
      });

      expect(item.borrowItemId, 21);
      expect(item.requestId, 5);
      expect(item.inventory?.brand, 'INGCO');
    });
  });
}
