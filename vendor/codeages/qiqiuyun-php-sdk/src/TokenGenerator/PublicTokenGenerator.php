<?php

namespace QiQiuYun\SDK\TokenGenerator;

use QiQiuYun\SDK;

class PublicTokenGenerator implements TokenGenerator
{
    protected $accessKey;

    protected $secretKey;

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePlayToken($resNo, $lifetime = 600, $once = true)
    {
        if ($once) {
            $once = SDK\random_str('16');
        } else {
            $once = 'no'; // no, mean "not once"
        }

        $deadline = time() + $lifetime;

        $signingText = "{$resNo}\n{$once}\n{$deadline}";

        $sign = hash_hmac('sha1', $signingText, $this->secretKey, true);

        $encodedSign = SDK\base64_urlsafe_encode($sign);

        return "{$once}:{$deadline}:{$encodedSign}";
    }
}
