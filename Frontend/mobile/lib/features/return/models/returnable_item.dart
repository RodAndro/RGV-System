/// An unreturned borrow item eligible for return.
class ReturnableItem {
  const ReturnableItem({
    required this.borrowItemId,
    required this.requestNumber,
    required this.requestId,
    required this.status,
    this.borrowDate,
    this.dueDate,
    required this.quantity,
    this.conditionBorrowed,
    this.inventory,
  });

  final int borrowItemId;
  final String requestNumber;
  final int requestId;
  final String status;
  final String? borrowDate;
  final String? dueDate;
  final int quantity;
  final String? conditionBorrowed;
  final ReturnableInventory? inventory;

  String get displayStatus => status == 'borrowed' ? 'approved' : status;

  factory ReturnableItem.fromJson(Map<String, dynamic> json) => ReturnableItem(
    borrowItemId: json['borrow_item_id'] as int,
    requestNumber: json['request_number'] as String? ?? '',
    requestId: json['request_id'] as int,
    status: json['status'] as String? ?? '',
    borrowDate: json['borrow_date'] as String?,
    dueDate: json['due_date'] as String?,
    quantity: json['quantity'] as int? ?? 1,
    conditionBorrowed: json['condition_borrowed'] as String?,
    inventory: json['inventory'] == null
        ? null
        : ReturnableInventory.fromJson(
            json['inventory'] as Map<String, dynamic>,
          ),
  );

  Map<String, dynamic> toJson() => {
    'borrow_item_id': borrowItemId,
    'request_number': requestNumber,
    'request_id': requestId,
    'status': status,
    'borrow_date': borrowDate,
    'due_date': dueDate,
    'quantity': quantity,
    'condition_borrowed': conditionBorrowed,
    'inventory': inventory?.toJson(),
  };
}

class ReturnableInventory {
  const ReturnableInventory({
    this.id,
    this.itemCode,
    this.name,
    this.brand,
    this.unit,
    this.location,
  });

  final int? id;
  final String? itemCode;
  final String? name;
  final String? brand;
  final String? unit;
  final String? location;

  factory ReturnableInventory.fromJson(Map<String, dynamic> json) =>
      ReturnableInventory(
        id: json['id'] as int?,
        itemCode: json['item_code'] as String?,
        name: json['name'] as String?,
        brand: json['brand'] as String?,
        unit: json['unit'] as String?,
        location: json['location'] as String?,
      );

  Map<String, dynamic> toJson() => {
    'id': id,
    'item_code': itemCode,
    'name': name,
    'brand': brand,
    'unit': unit,
    'location': location,
  };
}

/// Condition choices offered to the employee on return.
const List<String> returnConditions = [
  'new',
  'good',
  'fair',
  'poor',
  'damaged',
];
