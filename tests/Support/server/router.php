<?php

/**
 * The stub the feature tests send their requests to.
 *
 * It is run by PHP's built in web server and answers with what it received, so
 * that a test can check the request the package built out of its configuration.
 *
 * Routes:
 *   /            an echo of the request, as JSON
 *   /status/{n}  the same, with the status code {n}
 *   /slow        an echo of the request, after a second
 *   /redirect    a redirect to /
 *   /counter     the number of times it was asked, which grows with every call
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$status = 200;

if (preg_match('#^/status/([0-9]{3})$#', $path, $matches) === 1) {
    $status = (int) $matches[1];
}

if ($path === '/slow') {
    sleep(1);
}

if ($path === '/counter') {
    $file = sys_get_temp_dir().'/extractor-stub-counter';
    $count = ((int) @file_get_contents($file)) + 1;
    file_put_contents($file, (string) $count);

    header('Content-Type: application/json');

    echo json_encode(['count' => $count], JSON_THROW_ON_ERROR);

    exit;
}

if ($path === '/redirect') {
    header('Location: /', true, 302);

    exit;
}

$headers = [];

foreach ($_SERVER as $name => $value) {
    if (str_starts_with((string) $name, 'HTTP_')) {
        $headers[strtolower(str_replace('_', '-', substr((string) $name, 5)))] = $value;
    }
}

http_response_code($status);
header('Content-Type: application/json');
header('X-Stub-Server: extractor');

echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    'path' => $path,
    'query' => $_GET,
    'form' => $_POST,
    'headers' => $headers,
    'body' => file_get_contents('php://input'),
], JSON_THROW_ON_ERROR);
