import { createClient } from '@redis/client';
import http from 'node:http';
import process from 'node:process';
import { URL } from 'node:url';

const config = {
  REDIS_URL: process.env.REDIS_URL || 'redis://127.0.0.1:6379',
  PORT: Number(process.env.PORT) || 8080,
};

process.stdout.write(`Starting server with config: ${JSON.stringify(config)}\n`);

const redis = createClient({ url: config.REDIS_URL });
redis.on('connect', () => {
  process.stdout.write('Redis connected\n');
});
redis.on('error', (error) => {
  process.stderr.write(`Redis client error: ${error}\n`);
});
await redis.connect();

function fibo(n) {
  return n < 2 ? 1 : fibo(n - 2) + fibo(n - 1);
}

const server = http.createServer(async (req, res) => {
  const reqTime = new Date();

  res.on('finish', () => {
    process.stdout.write([
      reqTime.toISOString(),
      `"${req.method} ${req.url}"`,
      res.statusCode,
      `${new Date() - reqTime}ms`,
      `"${req.headers['user-agent'] ?? ''}"`,
    ].join(' ') + '\n');
  });

  try {
    const url = new URL(req.url, 'http://127.0.0.1');

    if (url.pathname === '/') {
      res.writeHead(200, {
        'Content-Type': 'text/plain',
      }).end('hello');
      return;
    }

    if (url.pathname === '/write') {
      const info = await Promise.all([...new Array(100).keys()].map(async (i) => {
        const key = `php-sucks:${(i).toString(10)}`;
        const value = new Date().toISOString();
        const result = await redis.set(key, value, { EX: 3600 });

        return { result, key, value };
      }));

      res.writeHead(200, { 'Content-Type': 'application/json' }).end(JSON.stringify(info));
      return;
    }

    if (url.pathname === '/read') {
      const reads = await Promise.all([...new Array(100).keys()].map(async (i) => {
        const key = `php-sucks:${(i).toString(10)}`;
        return {
          key,
          value: await redis.GET(key),
        };
      }));

      res.writeHead(200, { 'Content-Type': 'application/json' }).end(JSON.stringify(reads));
      return;
    }

    if (url.pathname === '/cpu') {
      const size = url.searchParams.has('size') ? Number(url.searchParams.get('size')) : 30;

      const start = Number(process.hrtime.bigint());
      const f = fibo(size);
      const end = Number(process.hrtime.bigint());

      const body = `Result: ${f}\nDuration: ${(end - start) / 1e9}ms\n`;

      res.writeHead(200, { 'Content-Type': 'text/plain' }).end(body);
      return;
    }

    res.writeHead(404, 'Not Found', {
      'Content-Type': 'text/plain',
    }).end('Not found');

  } catch (e) {
    res.writeHead(500, 'Internal Server Error', {
      'Content-Type': 'text/plain',
    }).end('Internal Server Error');

    process.stderr.write(`Handler error: ${e}\n`);
  }
});

process.on('SIGINT', (signal) => {
  process.stdout.write(`\n${signal} received, stopping server gracefully...\n`);
  server.close();
  redis.disconnect().then();
});

server.listen(config.PORT, () => {
  process.stdout.write(`Server listening on port ${config.PORT}\n`);
});
