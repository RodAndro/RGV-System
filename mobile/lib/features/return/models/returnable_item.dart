/// An unreturned borrow item eligible for return, plus the photo/condition
/// input captured on-device for submission.
class ReturnableItem {
  const ReturnableItem({
    required this.borrowItemId,
    required this.requestNumber,
    required this.requestId,
    this.borrowDate,
    this.dueDate,
    required this.quantity,
    this.conditionBorrowed,
    this.inventory,
  });

  final int borrowItemId;
  final String requestNumber;
  final int requestId;
  final String? borrowDate;
  final String? dueDate;
  final int quantity;
  final String? conditionBorrowed;
  final ReturnableInventory? inventory;

  factory ReturnableItem.fromJson(Map<String, dynamic> json) => ReturnableItem(
        borrowItemId: json['borrow_item_id'] as int,
        requestNumber: json['request_number'] as String? ?? '',
        requestId: json['request_id'] as int,
        borrowDate: json['borrow_date'] as String?,
        dueDate: json['due_date'] as String?,
        quantity: json['quantity'] as int? ?? 1,
        conditionBorrowed: json['condition_borrowed'] as String?,
        inventory: json['inventory'] == null
            ? null
            : ReturnableInventory.fromJson(json['inventory'] as Map<String, dynamic>),
      );
}

class ReturnableInventory {
  const ReturnableInventory({
    this.id,
    this.itemCode,
    this.name,
    this.brand,
    this.unit,
    this.imagePath,
  });

  final int? id;
  final String? itemCode;
  final String? name;
  final String? brand;
  final String? unit;
  final String? imagePath;

  factory ReturnableInventory.fromJson(Map<String, dynamic> json) =>
      ReturnableInventory(
        id: json['id'] as int?,
        itemCode: json['item_code'] as String?,
        name: json['name'] as String?,
        brand: json['brand'] as String?,
        unit: json['unit'] as String?,
        imagePath: json['image_path'] as String?,
      );
}

/// Condition choices offered to the employee on return.
const List<String> returnConditions = ['new', 'good', 'fair', 'poor', 'damaged'];
