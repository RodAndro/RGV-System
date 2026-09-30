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
        damageNotes: json['damage_notes'] as String?,
        returnEvidenceFileName: (json['return_evidence'] as Map<String, dynamic>?)?['file_name'] as String?,
      );
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
        adminRemarks: json['admin_remarks'] as String?,
        items: (json['items'] as List? ?? const [])
            .map((e) => BorrowItemLine.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}
