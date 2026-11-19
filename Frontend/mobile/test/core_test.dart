import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:rgv_employee_app/core/errors/api_call.dart';
import 'package:rgv_employee_app/core/errors/app_exception.dart';
import 'package:rgv_employee_app/core/config/app_config.dart';
import 'package:rgv_employee_app/core/theme/app_theme.dart';
import 'package:rgv_employee_app/core/network/api_endpoint_discovery.dart';
import 'package:rgv_employee_app/core/network/mobile_api_client.dart';
import 'package:rgv_employee_app/features/inventory/data/inventory_repository.dart';
import 'support/fake_mobile_api.dart';

void main() {
  test('physical-device debug builds use automatic API discovery', () {
    expect(AppConfig.usesAutomaticDiscovery, isTrue);
    expect(AppConfig.apiPrefix, '/api/v1/mobile');
    expect(() => AppConfig.validate(), returnsNormally);
  });

  group('API endpoint discovery', () {
    test('accepts the Laravel discovery response', () {
      expect(
        ApiEndpointDiscovery.parseResponse(
          '{"service":"rgv-mobile-api","version":1,"base_url":"http://192.168.1.20:8000"}',
        ),
        'http://192.168.1.20:8000',
      );
    });

    test('rejects unrelated, invalid, or non-local-service responses', () {
      expect(ApiEndpointDiscovery.parseResponse('not json'), isNull);
      expect(
        ApiEndpointDiscovery.parseResponse(
          '{"service":"other","version":1,"base_url":"http://192.168.1.20:8000"}',
        ),
        isNull,
      );
      expect(
        ApiEndpointDiscovery.parseResponse(
          '{"service":"rgv-mobile-api","version":1,"base_url":"https://example.com"}',
        ),
        isNull,
      );
    });
  });

  test(
    'mobile API client adds the Laravel mobile API prefix to requests',
    () async {
      late Uri requestedUri;
      final client = MobileApiClient(
        baseUrl: 'http://192.168.1.20:8000',
        httpClient: MockClient((request) async {
          requestedUri = request.url;
          return http.Response(
            jsonEncode({'token': 'test-token'}),
            200,
            headers: {'content-type': 'application/json'},
          );
        }),
      );

      await client.post('auth/login', authenticate: false);

      expect(requestedUri.path, '/api/v1/mobile/auth/login');
      client.close();
    },
  );

  test(
    'authenticated feature requests keep the bearer token and query',
    () async {
      late http.Request sentRequest;
      final client = MobileApiClient(
        baseUrl: 'http://192.168.1.20:8000',
        httpClient: MockClient((request) async {
          sentRequest = request;
          return http.Response(
            jsonEncode({'items': []}),
            200,
            headers: {'content-type': 'application/json'},
          );
        }),
      )..setToken('employee-token');

      await client.get('inventory/search', query: {'q': 'generator'});

      expect(sentRequest.url.path, '/api/v1/mobile/inventory/search');
      expect(sentRequest.url.queryParameters, {'q': 'generator'});
      expect(sentRequest.headers['Authorization'], 'Bearer employee-token');
      client.close();
    },
  );

  group('error envelope translation', () {
    test('maps a Laravel validation error to field errors', () {
      final response = http.Response(
        jsonEncode({
          'message': 'The given data was invalid.',
          'errors': {
            'email': ['The email field is required.'],
          },
        }),
        422,
        headers: {'content-type': 'application/json'},
      );

      expect(
        () => decodeJsonResponse(response),
        throwsA(
          isA<AppException>()
              .having((e) => e.statusCode, 'statusCode', 422)
              .having(
                (e) => e.fieldErrors['email'],
                'email field errors',
                contains('The email field is required.'),
              ),
        ),
      );
    });

    test('maps the versioned error envelope to a readable message', () {
      final response = http.Response(
        jsonEncode({
          'error': {
            'code': 'FORBIDDEN',
            'message': 'You do not have permission to perform this action.',
            'details': [],
          },
        }),
        403,
        headers: {'content-type': 'application/json'},
      );

      expect(
        () => decodeJsonResponse(response),
        throwsA(
          isA<AppException>()
              .having((e) => e.statusCode, 'statusCode', 403)
              .having((e) => e.isUnauthenticated, 'isUnauthenticated', isFalse),
        ),
      );
    });

    test('flags 401 as unauthenticated so the app can sign out', () {
      final response = http.Response(
        jsonEncode({'message': 'Unauthenticated.'}),
        401,
        headers: {'content-type': 'application/json'},
      );

      expect(
        () => decodeJsonResponse(response),
        throwsA(
          isA<AppException>().having(
            (e) => e.isUnauthenticated,
            'isUnauthenticated',
            isTrue,
          ),
        ),
      );
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
    final repo = InventoryRepository(FakeMobileApi());

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
