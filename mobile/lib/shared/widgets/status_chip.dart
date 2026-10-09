import 'package:flutter/material.dart';

import '../../core/theme/app_theme.dart';

/// Maps a borrow-request status to a color and a human label.
({String label, Color color, Color background}) _styleFor(String status) {
  switch (status.toLowerCase()) {
    case 'pending':
      return (label: 'Pending', color: const Color(0xFFD97706), background: const Color(0xFFFEF3C7));
    case 'approved':
      return (label: 'Approved', color: const Color(0xFF2563EB), background: const Color(0xFFEFF6FF));
    case 'rejected':
      return (label: 'Rejected', color: const Color(0xFFDC2626), background: const Color(0xFFFEE2E2));
    case 'borrowed':
      return (label: 'Borrowed', color: const Color(0xFFD97706), background: const Color(0xFFFEF3C7));
    case 'returned':
      return (label: 'Returned', color: const Color(0xFF2563EB), background: const Color(0xFFEFF6FF));
    case 'cancelled':
      return (label: 'Cancelled', color: const Color(0xFFDC2626), background: const Color(0xFFFEE2E2));
    default:
      return (label: status, color: RgvColors.slate, background: const Color(0xFFF1F5F9));
  }
}

class StatusChip extends StatelessWidget {
  const StatusChip({super.key, required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    final style = _styleFor(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: style.background,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        style.label,
        style: TextStyle(
          color: style.color,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}
