<?php

class ApiKeyController {

    public static function generateGuest($pdo) {

        $key = "GUEST_" . bin2hex(random_bytes(16));
        $expired = date("Y-m-d H:i:s", time() + 3600);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO api_keys (api_key, type, expired_at)
                 VALUES (?, 'guest', ?)"
            );
            $stmt->execute([$key, $expired]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Gagal membuat API key"
            ]);
            return;
        }

        echo json_encode([
            "success" => true,
            "api_key" => $key,
            "expired_at" => $expired
        ]);
    }
}
