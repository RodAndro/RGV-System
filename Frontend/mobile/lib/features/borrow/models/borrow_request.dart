class BorrowItemLine {
  const BorrowItemLine({
    required this.id,
    this.inventoryId,
    this.itemCode,
    this.name,
    this.brand,
    this.unit,
    required this.quantity,
    this.conditionBorrowed,
    this.conditionReturned,
    this.isReturned = false,
    this.returnedAt,
    this.returnLocation,
    this.damageNotes,
    this.returnEvidenceFileName,
  });

  final int id;
  final int? inventoryId;
  final String? itemCode;
  final String? name;
  final String? brand;
  final String? unit;
  final int quantity;
  final String? conditionBorrowed;
  final String? conditionReturned;
  final bool isReturned;
  final String? returnedAt;
  final String? returnLocation;
  final String? damageNotes;
  final String? returnEvidenceFileName;

  factory BorrowItemLine.fromJson(Map<String, dynamic> json) => BorrowItemLine(
    id: json['id'] as int,
    inventoryId: json['inventory_id'] as int?,
    itemCode: json['item_code'] as String?,
    name: json['name'] as String?,
    brand: json['brand'] as String?,
    unit: json['unit'] as String?,
    quantity: json['quantity'] as int? ?? 1,
    conditionBorrowed: json['condition_borrowed'] as String?,
    conditionReturned: json['condition_returned'] as String?,
    isReturned: json['is_returned'] as bool? ?? false,
    returnedAt: json['returned_at'] as String?,
    returnLocation: json['return_location'] as String?,
    damageNotes: json['damage_notes'] as String?,
    returnEvidenceFileName:
        (json['return_evidence'] as Map<String, dynamic>?)?['file_name']
            as String?,
  );

  Map<String, dynamic> toJson() => {
    'id': id,
    'inventory_id': inventoryId,
    'item_code': itemCode,
    'name': name,
    'brand': brand,
    'unit': unit,
    'quantity': quantity,
    'condition_borrowed': conditionBorrowed,
    'condition_returned': conditionReturned,
    'is_returned': isReturned,
    'returned_at': returnedAt,
    'return_location': returnLocation,
    'damage_notes': damageNotes,
  };
}

class BorrowRequest {
  const BorrowRequest({
    required this.id,
    required this.requestNumber,
    required this.status,
    this.reason,
    this.borrowDate,
    this.dueDate,
    this.returnDate,
    this.returnedAt,
    this.returnLocation,
    this.adminRemarks,
    this.items = const [],
  });

  final int id;
  final String requestNumber;
  final String status;
  final String? reason;
  final String? borrowDate;
  final String? dueDate;
  final String? returnDate;
  final String? returnedAt;
  final String? returnLocation;
  final String? adminRemarks;
  final List<BorrowItemLine> items;

  factory BorrowRequest.fromJson(Map<String, dynamic> json) => BorrowRequest(
    id: json['id'] as int,
    requestNumber: json['request_number'] as String? ?? '',
    status: json['status'] as String? ?? 'pending',
    reason: json['reason'] as String?,
    borrowDate: json['borrow_date'] as String?,
    dueDate: json['due_date'] as String?,
    returnDate: json['return_date'] as String?,
    returnedAt: json['returned_at'] as String?,
    returnLocation: json['return_location'] as String?,
    adminRemarks: json['admin_remarks'] as String?,
    items: (json['items'] as List? ?? const [])
        .map((e) => BorrowItemLine.fromJson(e as Map<String, dynamic>))
        .toList(),
  );

  Map<String, dynamic> toJson() => {
    'id': id,
    'request_number': requestNumber,
    'status': status,
    'reason': reason,
    'borrow_date': borrowDate,
    'due_date': dueDate,
    'return_date': returnDate,
    'returned_at': returnedAt,
    'return_location': returnLocation,
    'admin_remarks': adminRemarks,
    'items': items.map((item) => item.toJson()).toList(),
  };

  BorrowRequest copyWith({
    int? id,
    String? requestNumber,
    String? status,
    String? reason,
    String? borrowDate,
    String? dueDate,
    String? returnDate,
    String? returnedAt,
    String? returnLocation,
    String? adminRemarks,
    List<BorrowItemLine>? items,
  }) => BorrowRequest(
    id: id ?? this.id,
    requestNumber: requestNumber ?? this.requestNumber,
    status: status ?? this.status,
    reason: reason ?? this.reason,
    borrowDate: borrowDate ?? this.borrowDate,
    dueDate: dueDate ?? this.dueDate,
    returnDate: returnDate ?? this.returnDate,
    returnedAt: returnedAt ?? this.returnedAt,
    returnLocation: returnLocation ?? this.returnLocation,
    adminRemarks: adminRemarks ?? this.adminRemarks,
    items: items ?? this.items,
  );
}
