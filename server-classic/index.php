<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/vendor/autoload.php';

const DATE_ISO_JS = 'Y-m-d\TH:i:s.vp';

$config = [
    'REDIS_URL' => $_SERVER['REDIS_URL'] ?? 'redis://127.0.0.1:6379',
];

$stdout = fopen('php://stdout', 'a');
$stderr = fopen('php://stderr', 'a');

$redisConfig = parse_url($config['REDIS_URL']);

$redis = new Redis();
$redis->connect($redisConfig['host']);

function fibo(int $n): int
{
    return $n < 2 ? 1 : fibo($n - 2) + fibo($n - 1);
}

$requestHandler = new class ($redis) {
    public function __construct(
        private readonly Redis $redis,
    ) {}

    public function handle(Request $request): Response
    {
        $path = $request->getPathInfo();

        if ($path === '/') {
            return new Response(
                content: 'hello',
                headers: ['Content-Type' => 'text/plain']
            );
        }

        if ($path === '/write') {
            $info = [];

            for ($i = 0; $i < 100; $i++) {
                $key = "php-sucks:$i";
                $value = (new DateTimeImmutable())->format(DATE_ISO_JS);
                $result = $this->redis->set($key, $value, [
                    'ex' => 3600,
                ]);

                $info[] = [
                    'result' => $result,
                    'key'    => $key,
                    'value'  => $value,
                ];
            }

            return new JsonResponse(
                data: $info,
                headers: ['Content-Type' => 'application/json'],
            );
        }

        if ($path === '/read') {
            $reads = [];
            for ($i = 0; $i < 100; $i++) {
                $key = "php-sucks:$i";

                $reads[] = [
                    'key'   => $key,
                    'value' => $this->redis->get($key),
                ];
            }

            return new JsonResponse(
                data: $reads,
                headers: ['Content-Type' => 'application/json'],
            );
        }

        if ($path === '/cpu') {
            $size = $request->query->getInt('size', 30);
            $start = hrtime(true);
            $f = fibo($size);
            $end = hrtime(true);

            $duration = ($end - $start) / 1e9;

            $body = "Result: $f\nDuration: {$duration}ms\n";

            return new Response(
                content: $body,
                headers: ['Content-Type' => 'text/plain']
            );
        }

        return new Response(
            'Not found',
            Response::HTTP_NOT_FOUND,
            ['Content-Type' => 'text/plain'],
        );
    }
};

$request = Request::createFromGlobals();
try {
    $reqTime = new DateTimeImmutable();
    $response = $requestHandler->handle($request);
    $duration = (new DateTimeImmutable())->diff($reqTime, true);
    $durationFmt = $duration->f * 1e6;
    fwrite(
        $stdout,
        implode(' ', [
            $reqTime->format(DATE_ISO_JS),
            "\"{$request->getMethod()} {$request->getRequestUri()}\"",
            $response->getStatusCode(),
            "{$durationFmt}ms",
            $request->headers->get('user-agent', ''),
        ]),
    );
} catch (Throwable $t) {
    fwrite($stderr, "Handler error: {$t->getMessage()}\n");
    $response = new Response(
        'Internal server error',
        Response::HTTP_INTERNAL_SERVER_ERROR,
        ['Content-Type' => 'text/plain']
    );
}

$response->send();
