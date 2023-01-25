<?php

use Amp\Http\Server\Driver\SocketClientFactory;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\SocketHttpServer;
use Amp\Http\Status;
use Amp\Redis\Redis;
use Amp\Redis\RedisConfig;
use Amp\Redis\RedisSetOptions;
use Amp\Redis\RemoteExecutor;
use Amp\Serialization\JsonSerializer;
use Amp\Socket\InternetAddress;
use Amp\Socket\ResourceSocketServerFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Revolt\EventLoop;

use function Amp\async;
use function Amp\ByteStream\getStderr;
use function Amp\ByteStream\getStdout;
use function Amp\Future\awaitAll;
use function Amp\trapSignal;

require_once __DIR__ . '/vendor/autoload.php';

const DATE_ISO_JS = 'Y-m-d\TH:i:s.vp';

$config = [
    'REDIS_URL' => $_SERVER['REDIS_URL'] ?? 'redis://127.0.0.1:6379',
    'PORT'      => filter_var($_SERVER['PORT'] ?? 8080, FILTER_VALIDATE_INT),
];

$json = JsonSerializer::withAssociativeArrays();

$logger = new class($json) implements LoggerInterface {
    use LoggerTrait;

    public function __construct(private readonly JsonSerializer $json) {}

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $stream = $level === 'error' ? getStderr() : getStdout();
        $stream->write($message . ' ' . $this->json->serialize($context) . PHP_EOL);
    }
};

$redis = new Redis(new RemoteExecutor(RedisConfig::fromUri($config['REDIS_URL'])));

$logger->info('Starting server with config: ' . $json->serialize([...$config, 'loop' => EventLoop::getDriver()::class]));

function fibo(int $n): int
{
    return $n < 2 ? 1 : fibo($n - 2) + fibo($n - 1);
}

$server = new SocketHttpServer($logger, new ResourceSocketServerFactory(), new SocketClientFactory($logger));
$server->expose(new InternetAddress('0.0.0.0', $config['PORT']));

$server->onStart(static function () use ($logger, $config) {
    $logger->info("Server listening on port {$config['PORT']}");
});

$server->start(
    new class($logger, $redis, $json) implements RequestHandler {

        public function __construct(
            private readonly LoggerInterface $logger,
            private readonly Redis $redis,
            private readonly JsonSerializer $json,
        ) {}

        private function doHandle(Request $request): Response
        {
            $uri = $request->getUri();
            $path = $uri->getPath();

            if ($path === '/') {
                return new Response(headers: ['Content-Type' => 'text/plain'], body: 'hello');
            }

            if ($path === '/write') {
                $info = awaitAll(
                    array_map(fn ($i) => async(function ($i) {
                        $key = "php-sucks:$i";
                        $value = (new DateTimeImmutable())->format(DATE_ISO_JS);
                        $options = (new RedisSetOptions())->withTtl(3600);
                        $result = $this->redis->set($key, $value, $options);

                        return [
                            'result' => $result ? 'OK' : 'ERR',
                            'key'    => $key,
                            'value'  => $value,
                        ];
                    }, $i), range(0, 99)),
                );

                return new Response(
                    headers: ['Content-Type' => 'application/json'],
                    body: $this->json->serialize($info)
                );
            }

            if ($path === '/read') {
                $reads = awaitAll(
                    array_map(fn ($i) => async(function ($i) {
                        $key = "php-sucks:$i";

                        return [
                            'key'   => $key,
                            'value' => $this->redis->get($key),
                        ];
                    }, $i), range(0, 99)),
                );

                return new Response(
                    headers: ['Content-Type' => 'application/json'],
                    body: $this->json->serialize($reads)
                );
            }

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
                status: Status::NOT_FOUND,
                headers: ['Content-Type' => 'text/plain'],
                body: 'Not found'
            );
        }

        public
        function handleRequest(
            Request $request,
        ): Response {
            $reqTime = new DateTimeImmutable();
            $response = $this->doHandle($request);
            $duration = (new DateTimeImmutable())->diff($reqTime, true);
            $durationFmt = $duration->f * 1e6;
            $uri = $request->getUri();
            $pathInfo = $uri->getPath() . ($uri->getQuery() ? '?' . $uri->getQuery() : '');

            $this->logger->info(
                implode(' ', [
                    $reqTime->format(DATE_ISO_JS),
                    "\"{$request->getMethod()} {$pathInfo}\"",
                    $response->getStatus(),
                    "{$durationFmt}ms",
                    $request->getHeader('user-agent') ?? '',
                ]),
            );

            return $response;
        }
    },
    new class($logger) implements ErrorHandler {

        public function __construct(
            private readonly LoggerInterface $logger,
        ) {}

        public function handleError(int $status, ?string $reason = null, ?Request $request = null): Response
        {
            $this->logger->error("Handler error: [$status] $reason");

            return new Response(
                Status::INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'text/plain'],
            );
        }
    },
);

$signal = trapSignal([SIGINT, SIGTERM]);
$signalName = [SIGINT => 'SIGINT', SIGTERM => 'SIGTERM'][$signal];
$logger->info("\n$signalName received, stopping server gracefully...");
$server->stop();
