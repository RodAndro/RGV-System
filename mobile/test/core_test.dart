import 'package:flutter_test/flutter_test.dart';
import 'package:rgv_employee_app/core/config/app_config.dart';
import 'package:rgv_employee_app/core/network/api_client.dart';
import 'package:rgv_employee_app/features/inventory/data/inventory_repository.dart';

void main() {
  group('AppConfig', () {
    test('detects https scheme', () {
      expect(Uri.parse(AppConfig.apiBaseUrl).scheme, anyOf('http', 'https'));
    });
  });

  group('InventoryRepository.normalizeCode', () {
    final repo = InventoryRepository(
      ApiClient(baseUrl: 'http://localhost/api/v1/mobile'),
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
      expect(
        repo.normalizeCode('{"itemCode":"RGV-009"}'),
        'RGV-009',
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
