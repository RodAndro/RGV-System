import 'package:intl/intl.dart';

/// Formats an ISO date string (`2026-09-30`) into a readable label.
String formatDate(String? isoDate) {
  if (isoDate == null || isoDate.isEmpty) return '—';
  final date = DateTime.tryParse(isoDate);
  if (date == null) return isoDate;
  return DateFormat('MMM d, yyyy').format(date);
}

/// Formats an ISO-8601 timestamp into `Sep 30, 2026 2:15 PM`.
String formatDateTime(String? isoTimestamp) {
  if (isoTimestamp == null || isoTimestamp.isEmpty) return '—';
  final date = DateTime.tryParse(isoTimestamp);
  if (date == null) return isoTimestamp;
  return DateFormat('MMM d, yyyy h:mm a').format(date.toLocal());
}
