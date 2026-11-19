import dgram from 'node:dgram';

const discoveryPort = Number(process.env.RGV_MOBILE_DISCOVERY_PORT ?? 39555);
const apiPort = Number(process.env.RGV_MOBILE_API_PORT ?? 8000);
const discoveryRequest = 'RGV_MOBILE_API_DISCOVER_V1';
const serviceName = 'rgv-mobile-api';

const server = dgram.createSocket({ type: 'udp4', reuseAddr: true });

function addressForClient(client) {
  return new Promise((resolve, reject) => {
    const routeProbe = dgram.createSocket('udp4');

    routeProbe.once('error', (error) => {
      routeProbe.close();
      reject(error);
    });

    routeProbe.connect(client.port, client.address, () => {
      const address = routeProbe.address().address;
      routeProbe.close();
      resolve(address);
    });
  });
}

server.on('message', async (message, client) => {
  if (message.toString('utf8') !== discoveryRequest) return;

  try {
    const localAddress = await addressForClient(client);
    if (
      !localAddress ||
      localAddress === '0.0.0.0' ||
      localAddress.startsWith('127.') ||
      localAddress.startsWith('169.254.')
    ) {
      return;
    }

    const response = Buffer.from(
      JSON.stringify({
        service: serviceName,
        version: 1,
        base_url: `http://${localAddress}:${apiPort}`,
      }),
    );

    server.send(response, client.port, client.address, (error) => {
      if (error) console.error(`Could not reply to API discovery: ${error.message}`);
    });
  } catch (error) {
    console.error(`Could not resolve the route to ${client.address}: ${error.message}`);
  }
});

server.on('error', (error) => {
  console.error(`Mobile API discovery failed: ${error.message}`);
  process.exitCode = 1;
  server.close();
});

server.bind(discoveryPort, '0.0.0.0', () => {
  console.log(`Mobile API discovery listening on UDP ${discoveryPort}`);
});
