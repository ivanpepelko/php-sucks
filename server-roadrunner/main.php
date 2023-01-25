<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;

require_once __DIR__ . '/vendor/autoload.php';

const DATE_ISO_JS = 'Y-m-d\TH:i:s.vp';

$config = [
    'REDIS_URL' => $_SERVER['REDIS_URL'] ?? 'redis://127.0.0.1:6379',
    'PORT'      => filter_var($_SERVER['PORT'] ?? 8080, FILTER_VALIDATE_INT),
];

$worker = Worker::create();
$psrFactory = new Psr17Factory();

$psr7 = new PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

function fibo(int $n): int
{
    return $n < 2 ? 1 : fibo($n - 2) + fibo($n - 1);
}

$logger = new class implements LoggerInterface {
    use LoggerTrait;

    public function log($level, Stringable|string $message, array $context = []): void
    {
        // $stream = $level === 'error' ? STDERR : STDOUT;
        echo $message . ' ' . json_encode($context, JSON_THROW_ON_ERROR) . PHP_EOL;
    }
};

$requestHandler = new class($logger) {
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    private function doHandle(ServerRequestInterface $request): Response
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ($path === '/') {
            return new Response(headers: ['Content-Type' => 'text/plain'], body: 'hello');
        }

        // if ($path === '/write') {
        //     $info = awaitAll(
        //         array_map(fn ($i) => async(function ($i) {
        //             $key = "php-sucks:$i";
        //             $value = (new DateTimeImmutable())->format(DATE_ISO_JS);
        //             $options = (new RedisSetOptions())->withTtl(3600);
        //             $result = $this->redis->set($key, $value, $options);
        //
        //             return [
        //                 'result' => $result ? 'OK' : 'ERR',
        //                 'key'    => $key,
        //                 'value'  => $value,
        //             ];
        //         }, $i), range(0, 99)),
        //     );
        //
        //     return new Response(
        //         headers: ['Content-Type' => 'application/json'],
        //         body: $this->json->serialize($info)
        //     );
        // }
        //
        // if ($path === '/read') {
        //     $reads = awaitAll(
        //         array_map(fn ($i) => async(function ($i) {
        //             $key = "php-sucks:$i";
        //
        //             return [
        //                 'key'   => $key,
        //                 'value' => $this->redis->get($key),
        //             ];
        //         }, $i), range(0, 99)),
        //     );
        //
        //     return new Response(
        //         headers: ['Content-Type' => 'application/json'],
        //         body: $this->json->serialize($reads)
        //     );
        // }

        if ($path === '/cpu') {
            parse_str($uri->getQuery(), $params);
            $size = filter_var($params['size'] ?? 30, FILTER_VALIDATE_INT);

            $start = hrtime(true);
            $f = fibo($size);
            $end = hrtime(true);

            $duration = ($end - $start) / 1e9;

            $body = "Result: $f\nDuration: {$duration}ms\n";

            return new Response(headers: ['Content-Type' => 'text/plain'], body: $body);
        }

        return new Response(
            status: 400,
            headers: ['Content-Type' => 'text/plain'],
            body: 'Not found'
        );
    }

    public function handle(ServerRequestInterface $request): Response
    {
        $reqTime = new DateTimeImmutable();
        $response = $this->doHandle($request);
        $duration = (new DateTimeImmutable())->diff($reqTime, true);
        $durationFmt = $duration->f * 1e6;
        $uri = $request->getUri();
        $pathInfo = $uri->getPath() . ($uri->getQuery() ? '?' . $uri->getQuery() : '');

        $this->logger->info(
            implode(' ', [
                $reqTime->format(DATE_ISO_JS),
                "\"{$request->getMethod()} $pathInfo\"",
                $response->getStatusCode(),
                "{$durationFmt}ms",
                $request->getHeaderLine('user-agent') ?? '',
            ]),
        );

        return $response;
    }
};

while (true) {
    try {
        $request = $psr7->waitRequest();
        if (!($request instanceof ServerRequestInterface)) { // Termination request received
            break;
        }
        $response = $requestHandler->handle($request);
        $psr7->respond($response);
    } catch (\Throwable) {
        $psr7->respond(new Response(500, ['Content-Type' => 'text/plain'],));
    }
}
