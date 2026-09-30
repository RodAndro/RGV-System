import 'package:flutter/material.dart';

import '../../core/theme/app_theme.dart';

/// Centered spinner with an optional label.
class LoadingView extends StatelessWidget {
  const LoadingView({super.key, this.label});

  final String? label;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const CircularProgressIndicator(),
          if (label != null) ...[
            const SizedBox(height: 16),
            Text(label!, style: const TextStyle(color: RgvColors.slateLight)),
          ],
        ],
      ),
    );
  }
}

/// Error state with an optional retry action.
class ErrorView extends StatelessWidget {
  const ErrorView({super.key, required this.message, this.onRetry});

  final String message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.error_outline, color: RgvColors.danger, size: 48),
            const SizedBox(height: 16),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(color: RgvColors.slate, fontSize: 15),
            ),
            if (onRetry != null) ...[
              const SizedBox(height: 20),
              OutlinedButton.icon(
                onPressed: onRetry,
                icon: const Icon(Icons.refresh),
                label: const Text('Retry'),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// Empty state with an icon and message.
class EmptyState extends StatelessWidget {
  const EmptyState({super.key, required this.message, this.icon = Icons.inbox_outlined});

  final String message;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: RgvColors.slateLight, size: 48),
            const SizedBox(height: 16),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(color: RgvColors.slateLight, fontSize: 15),
            ),
          ],
        ),
      ),
    );
  }
}
