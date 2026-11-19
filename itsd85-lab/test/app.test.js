import assert from 'node:assert/strict';
import { after, before, describe, it } from 'node:test';
import { createServer } from 'node:http';
import app, { add, getVersion } from '../src/app.js';

const server = createServer(app);
let baseUrl;

before(async () => {
  await new Promise((resolve, reject) => {
    server.once('error', reject);
    server.listen(0, '127.0.0.1', resolve);
  });

  const address = server.address();
  baseUrl = `http://127.0.0.1:${address.port}`;
});

after(async () => {
  server.closeAllConnections();
  await new Promise((resolve, reject) => {
    server.close((error) => (error ? reject(error) : resolve()));
  });
});

describe('addition', () => {
  it('add(2, 3) returns 5', () => {
    assert.equal(add(2, 3), 5);
  });

  it('GET /api/add returns the sum as JSON', async () => {
    const response = await fetch(`${baseUrl}/api/add?a=2&b=3`);
    assert.equal(response.status, 200);
    assert.deepEqual(await response.json(), { a: 2, b: 3, result: 5 });
  });

  it('GET /api/add rejects a nonnumeric operand with HTTP 400', async () => {
    const response = await fetch(`${baseUrl}/api/add?a=x&b=2`);
    assert.equal(response.status, 400);
    assert.equal(typeof (await response.json()).error, 'string');
  });

  it('GET /api/add rejects missing and nonfinite operands', async () => {
    for (const query of ['a=2', 'a=Infinity&b=2', 'a=&b=2', 'a=1e308&b=1e308']) {
      const response = await fetch(`${baseUrl}/api/add?${query}`);
      assert.equal(response.status, 400, query);
    }
  });
});

describe('homepage and health endpoint', () => {
  it('GET / displays the lab heading and current version', async () => {
    const response = await fetch(baseUrl);
    const html = await response.text();
    assert.equal(response.status, 200);
    assert.match(html, /ITSD 85 CI\/CD Lab/);
    assert.match(html, new RegExp(`<strong id="app-version">${getVersion()}</strong>`));
  });

  it('GET /health returns HTTP 200 and the current version', async () => {
    const response = await fetch(`${baseUrl}/health`);
    assert.equal(response.status, 200);
    assert.deepEqual(await response.json(), { status: 'ok', version: getVersion() });
  });

  it('GET / and /health return APP_VERSION when supplied by the deployment', async () => {
    const originalVersion = process.env.APP_VERSION;
    process.env.APP_VERSION = 'test-commit-sha';

    try {
      const home = await fetch(baseUrl);
      const html = await home.text();
      const response = await fetch(`${baseUrl}/health`);
      assert.equal(home.status, 200);
      assert.match(html, /test-commit-sha/);
      assert.equal(response.status, 200);
      assert.deepEqual(await response.json(), { status: 'ok', version: 'test-commit-sha' });
    } finally {
      if (originalVersion === undefined) {
        delete process.env.APP_VERSION;
      } else {
        process.env.APP_VERSION = originalVersion;
      }
    }
  });
});
