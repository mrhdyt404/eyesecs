<?php

header('Content-Type: application/json');

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$endpoint = preg_replace('#^/api#', '', $uri);
$endpoint = $endpoint ?: '/';

/* ROOT */
if ($endpoint === '/') {
    echo json_encode([
        "service" => "Open URL Verification API",
        "version" => "v1",
        "status"  => "running"
    ]);
    exit;
}

/* GUEST API KEY */
if ($endpoint === '/v1/apikey/guest' && $method === 'POST') {
    ApiKeyController::generateGuest($pdo);
    exit;
}

/* URL CHECK */
if ($endpoint === '/v1/url/check' && $method === 'POST') {
    ApiKeyMiddleware::handle($pdo);
    UrlController::check($pdo);
    exit;
}

/* 404 */
http_response_code(404);
echo json_encode([
    "error"    => "Endpoint not found",
    "endpoint" => $endpoint,
    "method"   => $method
]);
