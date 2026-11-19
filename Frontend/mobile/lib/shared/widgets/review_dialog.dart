import 'package:flutter/material.dart';

import '../../core/theme/app_theme.dart';

class ReviewDialogRow {
  const ReviewDialogRow({required this.label, required this.value});

  final String label;
  final String value;
}

class ReviewDialog extends StatelessWidget {
  const ReviewDialog({
    super.key,
    required this.title,
    required this.confirmLabel,
    required this.rows,
    this.subtitle,
    this.cancelLabel = 'Cancel',
    this.isDestructive = false,
    this.showCloseButton = true,
  });

  final String title;
  final String? subtitle;
  final List<ReviewDialogRow> rows;
  final String cancelLabel;
  final String confirmLabel;
  final bool isDestructive;
  final bool showCloseButton;

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Dialog(
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      child: ConstrainedBox(
        constraints: BoxConstraints(
          maxWidth: 560,
          maxHeight: size.height * 0.82,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Padding(
              padding: EdgeInsets.fromLTRB(
                20,
                16,
                showCloseButton ? 12 : 20,
                12,
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: Theme.of(
                            context,
                          ).textTheme.titleLarge?.copyWith(fontSize: 20),
                        ),
                        if (subtitle != null && subtitle!.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            subtitle!,
                            style: const TextStyle(
                              color: RgvColors.slateLight,
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  if (showCloseButton)
                    IconButton(
                      tooltip: 'Close',
                      onPressed: () => Navigator.of(context).pop(false),
                      icon: const Icon(Icons.close),
                      visualDensity: VisualDensity.compact,
                    ),
                ],
              ),
            ),
            const Divider(height: 1),
            if (rows.isNotEmpty)
              Flexible(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                  child: Container(
                    decoration: BoxDecoration(
                      border: Border.all(color: RgvColors.cardBorder),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const _ReviewTableHeader(),
                        for (var index = 0; index < rows.length; index++) ...[
                          if (index > 0) const Divider(height: 1),
                          _ReviewTableRow(row: rows[index]),
                        ],
                      ],
                    ),
                  ),
                ),
              ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 10, 20, 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(false),
                    child: Text(cancelLabel),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      minimumSize: const Size(88, 42),
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      backgroundColor: isDestructive ? RgvColors.danger : null,
                    ),
                    onPressed: () => Navigator.of(context).pop(true),
                    child: Text(confirmLabel),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ReviewTableHeader extends StatelessWidget {
  const _ReviewTableHeader();

  @override
  Widget build(BuildContext context) => const ColoredBox(
    color: RgvColors.surface,
    child: Padding(
      padding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Row(
        children: [
          Expanded(
            flex: 4,
            child: Text(
              'Request detail',
              style: TextStyle(
                color: RgvColors.slate,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          Expanded(
            flex: 6,
            child: Text(
              'Provided information',
              style: TextStyle(
                color: RgvColors.slate,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    ),
  );
}

class _ReviewTableRow extends StatelessWidget {
  const _ReviewTableRow({required this.row});

  final ReviewDialogRow row;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          flex: 4,
          child: Text(
            row.label,
            style: const TextStyle(color: RgvColors.slateLight, fontSize: 13),
          ),
        ),
        Expanded(
          flex: 6,
          child: Text(
            row.value,
            style: const TextStyle(
              color: RgvColors.textPrimary,
              fontSize: 14,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
      ],
    ),
  );
}
