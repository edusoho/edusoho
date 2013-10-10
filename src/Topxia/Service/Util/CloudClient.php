<?php

namespace Topxia\Service\Util;

class CloudClient
{
	protected $accessKey;

	protected $secretKey;

    protected $uploadTokenDuration = 3600;

    protected $defaultBucket;

    protected $macIndex;

    protected $macKey;

    protected $defaultResponseData = array(
        'bucket' => '$(bucket)',
        'key' => '$(key)',
        'filename' => '$(fname)',
        'size' => '$(fsize)',
        'mimeType' => '$(mimeType)',
        'etag' => '$(etag)',
        'imageInfo' => '$(imageInfo)',
        'userId' => '$(endUser)',
        'filepath' => '$(x:filepath)',
    );

    public function __construct ($accessKey, $secretKey, $bucket = null, $bucketDomain = null, $macIndex = 1, $macKey = '')
    {
    	$this->accessKey = $accessKey;
    	$this->secretKey = $secretKey;
        $this->bucket = $bucket;
        $this->bucketDomain = rtrim($bucketDomain, '/');
        $this->macIndex = $macIndex;
        $this->macKey = $macKey;
    }

    public function generateUploadToken($policy = array())
    {
        $rawPolicy = $policy;

        $policy = array();
        $policy['scope'] = $this->bucket;
        $policy['deadline'] = empty($policy['deadline']) ? time()+3600 : intval($policy['deadline']);

        if (isset($rawPolicy['endUser'])) {
            $policy['endUser'] = (string) $rawPolicy['endUser'];
        }

        $notifyType = array_key_exists('callbackUrl', $policy) ? 'callback' : (array_key_exists('returnUrl', $policy) ? 'return' : null);
        if ($notifyType) {
            $policy["{$notifyType}Url"] = $rawPolicy["{$notifyType}Url"];
            $policy["{notifyType}Body"] = empty($rawPolicy["{$notifyType}Body"]) ? $this->defaultResponseData : $rawPolicy["{$notifyType}Body"];
            $policy["{notifyType}Body"] = $this->serializeUploadNotifyBodyPolicy($notifyType, $policy["{notifyType}Body"]);
        } else {
            $policy['returnBody'] = empty($rawPolicy['returnBody']) ? $this->defaultResponseData : $rawPolicy['returnBody'];
            $policy['returnBody'] = $this->serializeUploadNotifyBodyPolicy('return', $policy['returnBody']);
        }

    	$encodedPolicy = $this->encodeSafely(json_encode($policy));

		$sign = hash_hmac('sha1', $encodedPolicy, $this->secretKey, true);
		return $this->accessKey . ':' . $this->encodeSafely($sign) . ':' . $encodedPolicy;
    }

    public function generateDownloadCookieToken($url, $deadline = 0)
    {
        if (empty($deadline)) {
            $deadline = time() + 60;
        }

        $url = str_replace(array('http://', 'https://'), '' , $url);
        $url = str_replace('?', '\\\\?', $url);

        $policy = json_encode(array('E' => $deadline, 'S' => $url));

        $ctx = $this->encodeSafely($policy);
        $sign = hash_hmac('sha1', $ctx, $this->macKey, true);
        $sign = $this->encodeSafely($sign);

        return "{$this->macIndex}:{$sign}:{$ctx}";
    }

    public function getDownloadUrl($key)
    {
        return $this->bucketDomain . '/' . $key;
    }

    private function encodeSafely($string)
    {
		$find = array('+', '/');
		$replace = array('-', '_');
		return str_replace($find, $replace, base64_encode($string));
    }

    private function serializeUploadNotifyBodyPolicy($type, $policy)
    {
        $parts = array();
        if ($type == 'return') {
            foreach ($policy as $key => $value) {
                $parts[] = "\"{$key}\":{$value}";
            }
            $serialzePolicy = '{'. implode(',', $parts) . '}';
        } else {
            foreach ($policy as $key => $value) {
                $parts[] = "{$key}={$value}";
            }
            $serialzePolicy = implode('&', $parts);
        }
        return $serialzePolicy;
    }
}