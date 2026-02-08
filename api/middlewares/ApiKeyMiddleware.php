<?php
class ApiKeyMiddleware {

    public static function handle(PDO $pdo, ?string $requiredRole = null) {

        /* ================= API KEY ================= */
        $key = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (!$key) {
            self::abort(401, "API Key required");
        }

        $stmt = $pdo->prepare("
            SELECT * FROM api_keys
            WHERE api_key = ?
              AND status = 'active'
        ");
        $stmt->execute([$key]);
        $api = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$api) {
            self::abort(401, "Invalid or expired API Key");
        }

        /* ================= ROLE CHECK ================= */
        if ($requiredRole && $api['type'] !== $requiredRole) {
            self::abort(403, "Access denied");
        }

        $client_ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        /* ================= RATE LIMIT (REDIS) ================= */
        if ($api['type'] === 'guest' && (int)$api['rate_limit'] > 0) {

            try {
                $redis = new Redis();
                $redis->connect('127.0.0.1', 6379, 1.5);

                $date = date('Ymd');
                $redisKey = "rate:{$api['id']}:{$client_ip}:{$date}";

                // atomic increment
                $count = $redis->incr($redisKey);

                // set TTL hanya saat pertama kali dibuat
                if ($count === 1) {
                    $redis->expire($redisKey, 86400); // 24 jam
                }

                if ($count > (int)$api['rate_limit']) {
                    self::abort(
                        429,
                        "Rate limit {$api['rate_limit']} request/hari tercapai"
                    );
                }

            } catch (Exception $e) {
                // FAIL-OPEN (recommended): jika Redis mati, API tetap jalan
                error_log("Redis rate limit error: " . $e->getMessage());
            }
        }

        /* ================= GLOBAL CONTEXT ================= */
        $GLOBALS['api_key'] = [
            'id'    => $api['id'],
            'type'  => $api['type'],
            'owner' => $api['owner']
        ];
    }

    /* ================= HELPER ================= */
    private static function abort(int $code, string $message) {
        http_response_code($code);
        echo json_encode([
            "success" => false,
            "error"   => $message
        ]);
        exit;
    }
}
