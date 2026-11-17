import express from 'express';

const app = express();

export function getVersion() {
  return process.env.APP_VERSION?.trim() || 'dev';
}

export function add(a, b) {
  if (!Number.isFinite(a) || !Number.isFinite(b)) {
    throw new TypeError('Both operands must be finite numbers.');
  }

  const result = a + b;
  if (!Number.isFinite(result)) {
    throw new RangeError('The sum is outside the supported number range.');
  }

  return result;
}

function parseNumber(value) {
  if (typeof value !== 'string' || value.trim() === '') {
    return null;
  }

  const number = Number(value);
  return Number.isFinite(number) ? number : null;
}

function escapeHtml(value) {
  return value.replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  })[character]);
}

app.get('/', (_request, response) => {
  const version = escapeHtml(getVersion());
  response.type('html').send(`<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITSD 85 CI/CD Lab</title>
  </head>
  <body>
    <main>
      <h1>ITSD 85 CI/CD Lab</h1>
      <p>Application version: <strong id="app-version">${version}</strong></p>
    </main>
  </body>
</html>`);
});

app.get('/health', (_request, response) => {
  response.json({ status: 'ok', version: getVersion() });
});

app.get('/api/add', (request, response) => {
  const a = parseNumber(request.query.a);
  const b = parseNumber(request.query.b);

  if (a === null || b === null) {
    return response.status(400).json({
      error: 'Query parameters a and b must be finite numbers.',
    });
  }

  try {
    return response.json({ a, b, result: add(a, b) });
  } catch {
    return response.status(400).json({
      error: 'The sum is outside the supported number range.',
    });
  }
});

export default app;
