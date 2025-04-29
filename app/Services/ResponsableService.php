<?php

namespace App\Services;

class ResponsableService
{
    private const ALGORITHM = 'HS512';
    private const TYPE = 'JWT';
    private const EXPIRATION_TIME = 30 * 60; // 30 minutos

    public static function generateJWT($ci, $code)
    {
        $headers = [
            'alg' => self::ALGORITHM,
            'typ' => self::TYPE,
        ];

        $payload = [
            'sub' => $ci,
            'iat' => time(),
            'exp' => time() + self::EXPIRATION_TIME,
            'code' => $code,
        ];

        $encodedHeaders = self::base64UrlEncode(json_encode($headers));
        $encodedPayload = self::base64UrlEncode(json_encode($payload));

        $signatureInput = $encodedHeaders . "." . $encodedPayload;
        $secretKey = getenv('APP_KEY');
        $signature = hash_hmac('sha512', $signatureInput, $secretKey, true);
        $encodedSignature = self::base64UrlEncode($signature);

        $token = $encodedHeaders . "." . $encodedPayload . "." . $encodedSignature;
        return $token;
    }

    public static function decodeJWT($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) != 3) {
                throw new \InvalidArgumentException("Invalid JWT token format");
            }

            list($encodedHeaders, $encodedPayload, $encodedSignature) = $parts;

            $decodedHeaders = json_decode(self::base64UrlDecode($encodedHeaders), true);
            $decodedPayload = json_decode(self::base64UrlDecode($encodedPayload), true);

            if ($decodedHeaders === null || $decodedPayload === null) {
                throw new \InvalidArgumentException("Error parsing JWT token");
            }

            // Verificar la firma
            $signatureInput = $encodedHeaders . "." . $encodedPayload;
            $secretKey = getenv('APP_KEY');
            $expectedSignature = hash_hmac('sha512', $signatureInput, $secretKey, true);
            $expectedEncodedSignature = self::base64UrlEncode($expectedSignature);

            if (!hash_equals($expectedEncodedSignature, $encodedSignature)) {
                throw new \InvalidArgumentException("Invalid JWT token signature");
            }

            // Verificar la expiración
            if ($decodedPayload['exp'] < time()) {
                throw new \InvalidArgumentException("JWT token has expired");
            }

            return [
                "headers" => $decodedHeaders,
                "payload" => $decodedPayload,
            ];
        } catch (\Exception $e) {
            return ['errors' => ['jwt' => [$e->getMessage()]]];
        }
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
