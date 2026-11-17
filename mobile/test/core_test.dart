import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/core/errors/app_exception.dart';
import 'package:rgv_employee_app/core/errors/supabase_call.dart';
import 'package:rgv_employee_app/core/network/mobile_api_client.dart';
import 'package:rgv_employee_app/core/config/app_config.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/features/inventory/data/inventory_repository.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

void main() {
  test('explains when Supabase employee profile RPC is not installed', () async {
    final failure = PostgrestException(
      message:
          'Could not find the function public.mobile_employee_profile without parameters in the schema cache',
      code: 'PGRST202',
    );

    await expectLater(
      supabaseCall(() async => throw failure),
      throwsA(
        isA<AppException>().having(
          (error) => error.message,
          'message',
          contains('apply database/supabase/mobile_backend.sql'),
        ),
      ),
    );
  });

  test('Supabase configuration has valid defaults for local builds', () {
    expect(AppConfig.supabaseUrl, startsWith('https://'));
    expect(AppConfig.supabaseAnonKey, isNotEmpty);
    expect(() => AppConfig.validate(), returnsNormally);
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
      MobileApiClient(
        supabase: SupabaseClient(
          'https://example.supabase.co',
          'test-publishable-key',
          authOptions: const AuthClientOptions(autoRefreshToken: false),
        ),
      ),
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

    test('prefers the immutable QR token over mutable item code metadata', () {
      expect(
        repo.normalizeCode(
          '{"item_code":"OLD-CODE","qr_token":"QR-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"}',
        ),
        'QR-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
      );
    });

    test('prefers the exact database id over mutable item code metadata', () {
      expect(repo.normalizeCode('{"item_code":"OLD-CODE","id":42}'), '42');
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
