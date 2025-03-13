<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler {
    private static $secretKey = 'your-secret-key'; // 自定义密钥，需保密
    private static $algorithm = 'HS256';          // 加密算法

    // 生成 Token
    public static function createToken(array $payload): string {
        $payload += [
            'iss' => 'your-domain.com',  // 签发者
            'iat' => time(),             // 签发时间
            'exp' => time() + 3600,      // 过期时间（1小时）
            'nbf' => time() - 60         // 生效时间（允许1分钟时钟偏差）
        ];
        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function validateToken(string $token): ?object {
        try {
            return JWT::decode($token, self::$secretKey, [self::$algorithm]);
        } catch (\Exception $e) {
            return null;
        }
    }
}
