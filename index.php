<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

/* 1️⃣ Root → public */
if ($uri === '') {
    require __DIR__ . '/public/index.html';
    exit;
}

/* 2️⃣ Public files */
if (str_starts_with($uri, '/public')) {
    $file = __DIR__ . $uri;
    if (is_file($file)) {
        require $file;
        exit;
    }
    http_response_code(404);
    exit('File not found');
}

/* 3️⃣ API (FIXED) */
if (str_starts_with($uri, '/api')) {
    require __DIR__ . '/api/index.php';
    exit;
}

/* 4️⃣ Admin */
if (str_starts_with($uri, '../admin')) {
    require __DIR__ . '/admin/index.php';
    exit;
}

/* 5️⃣ Assets */
$file = __DIR__ . $uri;
if (is_file($file)) {
    readfile($file);
    exit;
}

/* 6️⃣ Fallback */
http_response_code(404);
echo '404 Not Found';
