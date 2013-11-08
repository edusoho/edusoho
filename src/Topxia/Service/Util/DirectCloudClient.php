<?php

namespace Topxia\Service\Util;
use Topxia\Service\Util\CloudClient;
use \RuntimeException;

class DirectCloudClient implements CloudClient
{
    protected $accessKey;

    protected $secretKey;

    protected $bucket;

    protected $bucketDomain; 

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
        'convertId' => '$(persistentId)',
        'convertKey' => '$(x:convertKey)',
    );

    public function __construct (array $options)
    {
        $this->accessKey = $options['accessKey'];
        $this->secretKey = $options['secretKey'];
        $this->bucket = $options['bucket'];
        $this->bucketDomain = rtrim($options['bucketDomain'], '/');
        $this->macIndex = $options['macIndex'];
        $this->macKey = $options['macKey'];
        $this->videoCommands = $options['videoCommands'];
        $this->audioCommands = $options['audioCommands'];
    }

    public function generateUploadToken($bucket, array $params = array())
    {

        $policy = array();
        $policy['scope'] = $bucket;
        $policy['deadline'] = time() + (empty($params['duration']) ? 3600 : intval($params['duration']));

        if (isset($params['user'])) {
            $policy['endUser'] = (string) $params['user'];
        }

        $policy['returnBody'] = $this->serializeUploadReturnBody($this->defaultResponseData);

        if (!empty($params['convertCommands']) and !empty($params['convertNotifyUrl'])) {
            $policy['PersistentOps'] = $params['convertCommands'];
            $policy['PersistentNotifyUrl'] = $params['convertNotifyUrl'];
        }

        $encodedPolicy = $this->encodeSafely(json_encode($policy));

        $sign = hash_hmac('sha1', $encodedPolicy, $this->secretKey, true);
        $token = $this->accessKey . ':' . $this->encodeSafely($sign) . ':' . $encodedPolicy;

        return array('token' => $token, 'url' => 'http://up.qiniu.com/');
    }

    public function download($bucket, $key, $duration = 3600)
    {
        $url = $this->bucketDomain . '/' . $key;
        $deadline = time() + ($duration ? 3600 : intval($duration));

        $policy = json_encode(array(
            'E' => $deadline,
            'S' => str_replace(array('http://', 'https://', '?'), array('', '', '\\\\?') , $url)
        ));

        $ctx = $this->encodeSafely($policy);

        $sign = $this->encodeSafely(hash_hmac('sha1', $ctx, $this->macKey, true));

        $cookie = "{$this->macIndex}:{$sign}:{$ctx}";

        $domain = $this->parseDomain($this->bucketDomain);

        setrawcookie('qiniuToken', $cookie, 0, '/', $domain, false, true);

        header("Location: $url");
        exit();
    }

   

    public function getBucket()
    {
        return $this->bucket;
    }

    public function getVideoConvertCommands()
    {
        return $this->videoCommands;
    }

    public function getAudioConvertCommands()
    {
        return $this->audioCommands;
    }

   

   

    public function getVideoInfo($bucket, $key)
    {
        $token = $this->generateViewToken($bucket, $key);

        $content = $this->getRequest($token['url'] . '?avinfo' , array(), $token['cookie']);
        $result = json_decode($content, true);

        if (empty($result)) {
            return null;
        }

        $info = array(
            'duration' => intval($result['format']['duration']),
            'filesize' => intval($result['format']['size'])
        );

        return $info;
    }

    public function getAudioInfo($bucket, $key)
    {
        return $this->getVideoInfo($bucket, $key);
    }

    public function removeFile($key){

    }



    public function getFileUrl($key,$targetId,$targetType){
        
    }



    private function generateViewToken($bucket, $key)
    {
        $params = array('bucket' => $bucket, 'key' => $key);
        $encodedParams = base64_encode(json_encode($params));

        $sign = hash_hmac('sha1', $encodedParams, $this->secretKey);
        $token = "{$this->accessKey}:{$encodedParams}:{$sign}";

        $content = $this->getRequest($this->getViewTokenUrl(), array('token' => $token));

        return json_decode($content, true);
    }

    private function parseDomain($url)
    {
        preg_match('/\w+\.\w+$/', $url, $matches);
        return $matches ? $matches[0] : null;
    }

    private function serializeUploadReturnBody($body)
    {
        $parts = array();

        foreach ($body as $key => $value) {
            $parts[] = "\"{$key}\":{$value}";
        }

        return '{'. implode(',', $parts) . '}';
    }

    private function encodeSafely($string)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($string));
    }

}