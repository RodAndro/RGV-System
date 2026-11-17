import app from './app.js';

const port = Number(process.env.PORT || 3000);

if (!Number.isInteger(port) || port < 1 || port > 65535) {
  throw new RangeError('PORT must be an integer from 1 to 65535.');
}

const server = app.listen(port, '0.0.0.0', () => {
  process.stdout.write(`ITSD 85 app ${process.env.APP_VERSION?.trim() || 'dev'} listening on port ${port}\n`);
});

function shutdown(signal) {
  process.stdout.write(`Received ${signal}; closing server.\n`);
  server.close((error) => {
    if (error) {
      process.stderr.write(`${error.message}\n`);
      process.exitCode = 1;
    }
  });
}

process.once('SIGINT', () => shutdown('SIGINT'));
process.once('SIGTERM', () => shutdown('SIGTERM'));
