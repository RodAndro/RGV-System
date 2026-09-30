class BorrowSummary {
  const BorrowSummary({
    required this.total,
    required this.pending,
    required this.approved,
    required this.borrowed,
    required this.returned,
    required this.overdue,
  });

  final int total;
  final int pending;
  final int approved;
  final int borrowed;
  final int returned;
  final int overdue;

  factory BorrowSummary.fromJson(Map<String, dynamic> json) => BorrowSummary(
        total: json['total'] as int? ?? 0,
        pending: json['pending'] as int? ?? 0,
        approved: json['approved'] as int? ?? 0,
        borrowed: json['borrowed'] as int? ?? 0,
        returned: json['returned'] as int? ?? 0,
        overdue: json['overdue'] as int? ?? 0,
      );
}

class RecentRequest {
  const RecentRequest({
    required this.id,
    required this.requestNumber,
    required this.status,
    this.borrowDate,
    this.dueDate,
    this.items = const [],
  });

  final int id;
  final String requestNumber;
  final String status;
  final String? borrowDate;
  final String? dueDate;
  final List<String> items;

  factory RecentRequest.fromJson(Map<String, dynamic> json) => RecentRequest(
        id: json['id'] as int,
        requestNumber: json['request_number'] as String? ?? '',
        status: json['status'] as String? ?? 'pending',
        borrowDate: json['borrow_date'] as String?,
        dueDate: json['due_date'] as String?,
        items: (json['items'] as List? ?? const [])
            .map((e) => e.toString())
            .toList(),
      );
}

class DashboardData {
  const DashboardData({
    required this.summary,
    required this.recentRequests,
  });

  final BorrowSummary summary;
  final List<RecentRequest> recentRequests;

  factory DashboardData.fromJson(Map<String, dynamic> json) => DashboardData(
        summary: BorrowSummary.fromJson(
          (json['summary'] as Map<String, dynamic>?) ?? const {},
        ),
        recentRequests: (json['recent_requests'] as List? ?? const [])
            .map((e) => RecentRequest.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}
