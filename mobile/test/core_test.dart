import 'dart:async';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/core/errors/app_exception.dart';
import 'package:rgv_employee_app/core/errors/supabase_call.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/features/inventory/data/inventory_repository.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

void main() {
  group('supabaseCall', () {
    test('converts transport failures to handled network errors', () async {
      for (final error in [
        SocketException('offline'),
        TimeoutException('timed out'),
      ]) {
        await expectLater(
          supabaseCall<void>(() async => throw error),
          throwsA(
            isA<AppException>().having(
              (exception) => exception.isNetworkError,
              'isNetworkError',
              isTrue,
            ),
          ),
        );
      }
    });
  });

  group('RGV theme', () {
    test('uses the website brand teal as its primary color', () {
      final theme = buildAppTheme();

      expect(RgvColors.primary, const Color(0xFF0F6F9D));
      expect(theme.colorScheme.primary, RgvColors.primary);
      expect(theme.appBarTheme.backgroundColor, RgvColors.primary);
    });
  });

  group('InventoryRepository.normalizeCode', () {
    final repo = InventoryRepository(
      SupabaseClient('https://example.supabase.co', 'test-publishable-key'),
    );

    test('returns plain codes unchanged', () {
      expect(repo.normalizeCode('RGV-009'), 'RGV-009');
      expect(repo.normalizeCode('  RGV-009  '), 'RGV-009');
    });

    test('extracts item_code from JSON metadata', () {
      expect(
        repo.normalizeCode('{"item_code":"RGV-009","name":"Stale"}'),
        'RGV-009',
      );
    });

    test('extracts camelCase itemCode from JSON metadata', () {
      expect(repo.normalizeCode('{"itemCode":"RGV-009"}'), 'RGV-009');
    });

    test('extracts the immutable token from an admin-generated QR URL', () {
      const token = 'QR-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
      expect(
        repo.normalizeCode('https://example.test/inventory/qr/$token'),
        token,
      );
    });

    test('falls back to numeric id from JSON', () {
      expect(repo.normalizeCode('{"id":9999}'), '9999');
    });

    test('treats malformed JSON as a plain code', () {
      expect(repo.normalizeCode('{not json'), '{not json');
    });
  });
}
