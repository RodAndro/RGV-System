import 'package:flutter/material.dart';

import '../../core/theme/app_theme.dart';

/// Section title styled after the bordered headings used in the Laravel UI.
class RgvSectionHeading extends StatelessWidget {
  const RgvSectionHeading({
    super.key,
    required this.title,
    this.icon,
    this.trailing,
  });

  final String title;
  final IconData? icon;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.only(bottom: 8),
      decoration: const BoxDecoration(
        border: Border(
          bottom: BorderSide(color: RgvColors.sectionBorder, width: 2),
        ),
      ),
      child: Row(
        children: [
          icon == null
              ? const SizedBox.shrink()
              : Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(icon!, color: RgvColors.primary, size: 19),
                    const SizedBox(width: 10),
                  ],
                ),
          Expanded(
            child: Text(title, style: Theme.of(context).textTheme.titleLarge),
          ),
          trailing ?? const SizedBox.shrink(),
        ],
      ),
    );
  }
}
