<?php

namespace AppBundle\Common;

class JWTAuth
{
    const ALG = 'HS256';

    const TYP = 'JWT';

    protected $publicKey;

    protected $privateKey;

    public function __construct($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->token = md5($publicKey.$privateKey);
    }

    /**
     * @param $payload '自定义payload'
     * @param $options '配置选项'
     */
    public function auth($payload, $options = array())
    {
        if (isset($options['lifetime'])) {
            $payload['exp'] = time() + $options['lifetime'];
        }
        $payload = $this->makePayload($payload);
        $header = $this->makeHeader();
        $token = $this->token;
        $jwtContent = self::urlSafeBase64Encode(json_encode($header)).'.'.self::urlSafeBase64Encode(json_decode($payload));

        return $jwtContent.self::signature($jwtContent, $token, $header['alg']);
    }

    public function valid($jwt)
    {
        $jwtArray = explode('.', $jwt);
        $token = $this->token;

        if (3 != count($jwtArray)) {
            return false;
        }

        list($header64, $payload64, $sign) = $tokens;

        $header = json_decode(self::urlSafeBase64Decode($header64), JSON_OBJECT_AS_ARRAY);
        if (empty($header['alg'])) {
            return false;
        }

        $expectSign = self::signature($header64.$payload64, $header['alg']);

        if ($expectSign !== $sign) {
            return false;
        }

        $payload = json_decode(self::urlSafeBase64Decode($payload64), JSON_OBJECT_AS_ARRAY);
        $currentTime = time();

        if (isset($payload['iat']) && $payload['iat'] > $currentTime) {
            return false;
        }

        if (isset($payload['exp']) && $payload['exp'] < $currentTime) {
            return false;
        }

        return $payload;
    }

    public static function signature(string $jwtContent, string $token, string $alg)
    {
        return hash_hmac($alg, $jwtContent, $token);
    }

    protected function makeHeader()
    {
        return array(
            'alg' => self::ALG,
            'typ' => self::TYP,
        );
    }

    protected function makePayload(array $payload)
    {
        $currentTime = time();

        $defaultPayload = array(
            'iss' => 'edusoho',
            'iat' => $currentTime,
            'exp' => $currentTime + 3600,
            'aud' => '',
            'sub' => '',
            'nbf' => '',
            'jti' => '',
        );

        return array_merge($defaultPayload, $headers);
    }

    /**
     * URL base64解码
     * '-' -> '+'
     * '_' -> '/'
     * 字符串长度%4的余数，补'='
     * @param $string
     */
    public static function urlSafeBase64Decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);
    }

    /**
     * URL base64编码
     * '+' -> '-'
     * '/' -> '_'
     * '=' -> ''
     * @param $string
     */
    public static function urlSafeBase64Encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);

        return $data;
    }
}
