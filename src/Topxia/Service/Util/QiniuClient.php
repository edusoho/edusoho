<?php

namespace Topxia\Service\Util;

class QiniuClient
{

	protected $accessKey;

	protected $secretKey;

    protected $uploadTokenDuration = 3600;

    protected $defaultBucket;

    protected $defaultResponseData = array(
        'key' => '$(key)',
        'originalName' => '$(fname)',
        'size' => '$(fsize)',
        'mimeType' => '$(mimeType)',
        'etag' => '$(etag)',
        'imageInfo' => '$(imageInfo)',
        'endUser' => '$(endUser)',
    );

    public function __construct ($accessKey, $secretKey, $defaultBucket = null)
    {
    	$this->accessKey = $accessKey;
    	$this->secretKey = $secretKey;
        $this->defaultBucket = $defaultBucket;
    }

    public function generateUploadToken($params = array())
    {
        if (empty($params['scope'])) {
            if (empty($this->defaultBucket)) {
                throw \InvalidArgumentException('默认空间(defaultBucket)未设置，scope参数不能为空。');
            }
            $params['scope'] = $this->defaultBucket;
        }

        if (empty($params['deadline'])) {
            $params['deadline'] = time() + 3600;
        }

        $availableParamKeys = array('scope', 'deadline', 'endUser', 'returnUrl', 'returnBody', 'callbackBody', 'callbackUrl', 'asyncOps');
        $notAllowedParamKeys = array_diff(array_keys($params), $availableParamKeys);
        if ($notAllowedParamKeys) {
            $notAllowedParamKeys = implode(',', $notAllowedParamKeys);
            throw \InvalidArgumentException('参数{$notAllowedParamKeys}非法。');
        }

        if (array_key_exists('callbackUrl', $params)) {
            if (empty($params['callbackBody'])) {
                $params['callbackBody'] = $this->defaultResponseData;
            }
            $params['callbackBody'] = $this->serializeCallbackBody($params['callbackBody']);
        } else {
            if (empty($params['returnBody'])) {
                $params['returnBody'] = $this->defaultResponseData;
            }
            $params['returnBody'] = $this->serializeReturnBody($params['returnBody']);
        }

        // var_dump($params);exit();

    	$encodedParams = json_encode($params);
    	$encodedParams = $this->encodeSafely($encodedParams);

		$sign = hash_hmac('sha1', $encodedParams, $this->secretKey, true);
		return $this->accessKey . ':' . $this->encodeSafely($sign) . ':' . $encodedParams;
    }

    private function encodeSafely($string)
    {
		$find = array('+', '/');
		$replace = array('-', '_');
		return str_replace($find, $replace, base64_encode($string));
    }

    private function serializeCallbackBody($data)
    {
        $parts = array();
        foreach ($data as $key => $value) {
            $parts[] = "{$key}={$value}";
        }
        return implode('$', $parts);
    }

    private function serializeReturnBody($data)
    {
        $parts = array();
        foreach ($data as $key => $value) {
            $parts[] = "\"{$key}\":{$value}";
        }
        return '{'. implode(',', $parts) . '}';
    }
}