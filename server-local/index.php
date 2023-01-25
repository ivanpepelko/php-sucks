<?php

$env = $_ENV['ENVIRONMENT'] ?? 'dev';

require_once __DIR__ . '/html_helper.php';

try {
    $url = parse_url($_SERVER['REQUEST_URI']);

    require match ($url['path']) {
        '/' => __DIR__ . '/root.php',
    };
} catch (UnhandledMatchError) {
    header('HTTP/1.1 404');
    header('Content-Type: text/plain');
    echo 'Not found';
} catch (Throwable $t) {
    header('HTTP/1.1 500');
    header('Content-Type: text/plain');

    if ($env === 'dev') {
        var_dump($t);
    } else { // prod
        echo 'Internal server error';
    }
}
