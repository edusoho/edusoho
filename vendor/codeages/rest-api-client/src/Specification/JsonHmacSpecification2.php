<?php
namespace Codeages\RestApiClient\Specification;

class JsonHmacSpecification2 implements Specification
{
    protected $algo;

    public function __construct($algo = 'sha1')
    {
        $this->algo = $algo;
    }

    public function getHeaders($token, $requestId = '')
    {
        $headers   = array();
        $headers[] = 'Content-type: application/json';
        $headers[] = "Authorization: Signature {$token}";
        $headers[] = "X-Request-Id: {$requestId}";
        return $headers;
    }

    public function packToken($config, $url, $body, $deadline, $once)
    {
        $signature = $this->signature($config, $url, $body, $deadline, $once);
        return "{$config['accessKey']}:{$deadline}:{$once}:{$signature}";
    }

    public function unpackToken($string)
    {
        $token = explode(':', $string);
        if (count($token) !== 4) {
            throw new \InvalidArgumentException('token invalid.');
        }

        return array(
            'accessKey' => $token[0],
            'deadline'  => $token[1],
            'once'      => $token[2],
            'signature' => $token[3]
        );
    }

    public function signature($config, $url, $body, $deadline, $once)
    {
        $data      = implode("\n", array($once, $deadline, $url, $body));
        $signature = hash_hmac($this->algo, $data, $config['secretKey'], true);
        $signature = str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));
        return $signature;
    }

    public function serialize($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException("In json hmac specification serialize data must be array.");
        }

        ksort($data);

        $json = json_encode($data);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_encode error: '.json_last_error_msg());
        }

        return $json;
    }

    public function unserialize($data)
    {
        $data = json_decode($data, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            if (function_exists('json_last_error_msg')) {
                $error = json_last_error_msg();
            } else {
                switch (json_last_error()) {
                    case JSON_ERROR_DEPTH:
                        $error = 'Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $error = 'Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $error = 'Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $error = 'Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        $error = '';
                }
            }
            throw new \InvalidArgumentException(
                'json_decode error: '.$error);
        }

        return $data;
    }
}