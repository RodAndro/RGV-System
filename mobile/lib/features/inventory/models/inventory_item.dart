class InventoryItem {
  const InventoryItem({
    required this.id,
    required this.itemCode,
    required this.name,
    this.brand,
    this.description,
    this.category,
    required this.quantity,
    this.unit = 'pcs',
    this.status = 'available',
    this.condition = 'good',
    this.imagePath,
    this.isActive = true,
  });

  final int id;
  final String itemCode;
  final String name;
  final String? brand;
  final String? description;
  final String? category;
  final int quantity;
  final String unit;
  final String status;
  final String condition;
  final String? imagePath;
  final bool isActive;

  bool get isAvailable => isActive && status == 'available';

  factory InventoryItem.fromJson(Map<String, dynamic> json) => InventoryItem(
        id: json['id'] as int,
        itemCode: json['item_code'] as String? ?? '',
        name: json['name'] as String? ?? '',
        brand: json['brand'] as String?,
        description: json['description'] as String?,
        category: json['category'] as String?,
        quantity: json['quantity'] as int? ?? 0,
        unit: json['unit'] as String? ?? 'pcs',
        status: json['status'] as String? ?? 'available',
        condition: json['condition'] as String? ?? 'good',
        imagePath: json['image_path'] as String?,
        isActive: json['is_active'] as bool? ?? true,
      );
}
