import 'dart:async';
import 'dart:convert';
import 'dart:io';

/// Finds the Laravel dev server on the phone's current local network.
///
/// The Laravel project starts a small UDP responder alongside `artisan serve`.
/// The responder replies with the address of the interface it used to reach
/// this phone, so this continues to work when the host changes Wi-Fi networks.
class ApiEndpointDiscovery {
  ApiEndpointDiscovery({this.timeout = const Duration(seconds: 2)});

  static const int discoveryPort = 39555;
  static const String requestMessage = 'RGV_MOBILE_API_DISCOVER_V1';

  final Duration timeout;
  Future<String>? _pendingDiscovery;

  Future<String> resolve() {
    final pending = _pendingDiscovery;
    if (pending != null) return pending;

    final discovery = _discover();
    _pendingDiscovery = discovery;
    return discovery.whenComplete(() {
      if (identical(_pendingDiscovery, discovery)) {
        _pendingDiscovery = null;
      }
    });
  }

  Future<String> _discover() async {
    late final RawDatagramSocket socket;

    try {
      socket = await RawDatagramSocket.bind(InternetAddress.anyIPv4, 0);
    } on SocketException catch (error) {
      throw ApiEndpointDiscoveryException(
        'Local network discovery failed: ${error.message}',
      );
    }

    socket.broadcastEnabled = true;
    final response = Completer<String>();
    late final StreamSubscription<RawSocketEvent> subscription;

    subscription = socket.listen((event) {
      if (event != RawSocketEvent.read) return;

      Datagram? datagram;
      while ((datagram = socket.receive()) != null) {
        final discovered = parseResponse(
          utf8.decode(datagram!.data, allowMalformed: true),
        );
        if (discovered != null && !response.isCompleted) {
          response.complete(discovered);
        }
      }
    });

    try {
      final requestBytes = utf8.encode(requestMessage);
      final destination = InternetAddress('255.255.255.255');
      final sent = socket.send(requestBytes, destination, discoveryPort);
      if (sent == 0) {
        throw const ApiEndpointDiscoveryException(
          'Could not send a Laravel discovery request on this network.',
        );
      }

      // Wi-Fi access points and mobile OS network stacks can drop an
      // occasional broadcast. Retry briefly while listening on the same
      // socket so every API call does not fail on a single lost datagram.
      final retries = [
        Timer(const Duration(milliseconds: 350), () {
          _retrySend(socket, requestBytes, destination);
        }),
        Timer(const Duration(milliseconds: 850), () {
          _retrySend(socket, requestBytes, destination);
        }),
      ];

      try {
        return await response.future.timeout(timeout);
      } finally {
        for (final retry in retries) {
          retry.cancel();
        }
      }
    } on TimeoutException {
      throw const ApiEndpointDiscoveryException(
        'Laravel was not found on this local network. Keep the phone and '
        'computer on the same Wi-Fi and run `composer run dev` in Backend.',
      );
    } on SocketException catch (error) {
      throw ApiEndpointDiscoveryException(
        'Could not search this local network: ${error.message}',
      );
    } finally {
      await subscription.cancel();
      socket.close();
    }
  }

  void _retrySend(
    RawDatagramSocket socket,
    List<int> requestBytes,
    InternetAddress destination,
  ) {
    try {
      socket.send(requestBytes, destination, discoveryPort);
    } on SocketException {
      // The primary request or a previous retry may have already received a
      // response while the socket was being closed.
    }
  }

  /// Parses and validates a discovery reply. Returns the server origin only.
  static String? parseResponse(String message) {
    try {
      final decoded = jsonDecode(message);
      if (decoded is! Map<String, dynamic> ||
          decoded['service'] != 'rgv-mobile-api' ||
          decoded['version'] != 1) {
        return null;
      }

      final rawUrl = decoded['base_url'];
      if (rawUrl is! String) return null;

      final uri = Uri.tryParse(rawUrl);
      if (uri == null ||
          uri.scheme != 'http' ||
          !uri.hasAuthority ||
          uri.host.isEmpty ||
          uri.port != 8000 ||
          uri.path.isNotEmpty ||
          uri.userInfo.isNotEmpty) {
        return null;
      }

      return '${uri.scheme}://${uri.authority}';
    } on FormatException {
      return null;
    }
  }
}

class ApiEndpointDiscoveryException implements Exception {
  const ApiEndpointDiscoveryException(this.message);

  final String message;
}
