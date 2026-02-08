<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-API-KEY");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middlewares/ApiKeyMiddleware.php';
require_once __DIR__ . '/controllers/UrlController.php';
require_once __DIR__ . '/controllers/ApiKeyController.php';
require_once __DIR__ . '/routes.php';
