<?php

namespace Omnipay\Alipay\Common;

use Exception;
/**
 * Sign Tool for Alipay
 * Class Signer
 * @package Omnipay\Alipay\Common
 */
class Signer
{
    const ENCODE_POLICY_QUERY = 'QUERY';
    const ENCODE_POLICY_JSON = 'JSON';
    const KEY_TYPE_PUBLIC = 1;
    const KEY_TYPE_PRIVATE = 2;
    protected $ignores = array('sign', 'sign_type');
    protected $sort = true;
    protected $encodePolicy = self::ENCODE_POLICY_QUERY;
    /**
     * @var array
     */
    private $params;
    public function __construct(array $params = array())
    {
        $this->params = $params;
    }
    public function signWithMD5($key)
    {
        $content = $this->getContentToSign();
        return md5($content . $key);
    }
    public function getContentToSign()
    {
        $params = $this->getParamsToSign();
        if ($this->encodePolicy == self::ENCODE_POLICY_QUERY) {
            return urldecode(http_build_query($params));
        } elseif ($this->encodePolicy == self::ENCODE_POLICY_JSON) {
            return json_encode($params);
        } else {
            return null;
        }
    }
    /**
     * @return mixed
     */
    public function getParamsToSign()
    {
        $params = $this->params;
        $this->unsetKeys($params);
        $params = $this->filter($params);
        if ($this->sort) {
            $this->sort($params);
        }
        return $params;
    }
    /**
     * @param $params
     */
    protected function unsetKeys(&$params)
    {
        foreach ($this->getIgnores() as $key) {
            unset($params[$key]);
        }
    }
    /**
     * @return array
     */
    public function getIgnores()
    {
        return $this->ignores;
    }
    /**
     * @param array $ignores
     *
     * @return $this
     */
    public function setIgnores($ignores)
    {
        $this->ignores = $ignores;
        return $this;
    }
    private function filter($params)
    {
        return array_filter($params, 'strlen');
    }
    /**
     * @param $params
     */
    protected function sort(&$params)
    {
        ksort($params);
    }
    public function signWithRSA($privateKey, $alg = OPENSSL_ALGO_SHA1)
    {
        $content = $this->getContentToSign();
        $sign = $this->signContentWithRSA($content, $privateKey, $alg);
        return $sign;
    }
    public function signContentWithRSA($content, $privateKey, $alg = OPENSSL_ALGO_SHA1)
    {
        $privateKey = $this->prefix($privateKey);
        $privateKey = $this->format($privateKey, self::KEY_TYPE_PRIVATE);
        $res = openssl_pkey_get_private($privateKey);
        $sign = null;
        try {
            openssl_sign($content, $sign, $res, $alg);
        } catch (\Exception $e) {
            if ($e->getCode() == 2) {
                $message = $e->getMessage();
                $message .= '
应用私钥格式有误，见 https://github.com/lokielse/omnipay-alipay/wiki/FAQs';
                throw new \Exception($message, $e->getCode(), $e);
            }
        }
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }
    /**
     * Prefix the key path with 'file://'
     *
     * @param $key
     *
     * @return string
     */
    private function prefix($key)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN' && is_file($key) && substr($key, 0, 7) != 'file://') {
            $key = 'file://' . $key;
        }
        return $key;
    }
    /**
     * Convert key to standard format
     *
     * @param $key
     * @param $type
     *
     * @return string
     */
    public function format($key, $type)
    {
        if (is_file($key)) {
            $key = file_get_contents($key);
        }
        if (is_string($key) && strpos($key, '-----') === false) {
            $key = $this->convertKey($key, $type);
        }
        return $key;
    }
    /**
     * Convert one line key to standard format
     *
     * @param $key
     * @param $type
     *
     * @return string
     */
    public function convertKey($key, $type)
    {
        $lines = array();
        if ($type == self::KEY_TYPE_PUBLIC) {
            $lines[] = '-----BEGIN PUBLIC KEY-----';
        } else {
            $lines[] = '-----BEGIN RSA PRIVATE KEY-----';
        }
        for ($i = 0; $i < strlen($key); $i += 64) {
            $lines[] = trim(substr($key, $i, 64));
        }
        if ($type == self::KEY_TYPE_PUBLIC) {
            $lines[] = '-----END PUBLIC KEY-----';
        } else {
            $lines[] = '-----END RSA PRIVATE KEY-----';
        }
        return implode('
', $lines);
    }
    public function verifyWithMD5($content, $sign, $key)
    {
        return md5($content . $key) == $sign;
    }
    public function verifyWithRSA($content, $sign, $publicKey, $alg = OPENSSL_ALGO_SHA1)
    {
        $publicKey = $this->prefix($publicKey);
        $publicKey = $this->format($publicKey, self::KEY_TYPE_PUBLIC);
        $res = openssl_pkey_get_public($publicKey);
        if (!$res) {
            $message = 'The public key is invalid';
            $message .= '
支付宝公钥格式有误，见 https://github.com/lokielse/omnipay-alipay/wiki/FAQs';
            throw new \Exception($message);
        }
        $result = (bool) openssl_verify($content, base64_decode($sign), $res, $alg);
        openssl_free_key($res);
        return $result;
    }
    /**
     * @param boolean $sort
     *
     * @return Signer
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
        return $this;
    }
    /**
     * @param int $encodePolicy
     *
     * @return Signer
     */
    public function setEncodePolicy($encodePolicy)
    {
        $this->encodePolicy = $encodePolicy;
        return $this;
    }
}