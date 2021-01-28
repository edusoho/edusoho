<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\Exception\NetworkException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractAopRequest extends AbstractRequest
{
    protected $method;

    protected $privateKey;

    protected $encryptKey;

    protected $alipayPublicKey;

    protected $endpoint = 'https://openapi.alipay.com/gateway.do';

    protected $returnable = false;

    protected $notifiable = false;


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validateParams();

        $this->setDefaults();

        $this->convertToString();

        $data = $this->parameters->all();

        $data['method'] = $this->method;

        ksort($data);

        $data['sign'] = $this->sign($data, $this->getSignType());

        return $data;
    }


    /**
     * @throws InvalidRequestException
     */
    public function validateParams()
    {
        $this->validate(
            'app_id',
            'format',
            'charset',
            'sign_type',
            'timestamp',
            'version',
            'biz_content'
        );
    }


    protected function setDefaults()
    {
        if (! $this->getTimestamp()) {
            $this->setTimestamp(date('Y-m-d H:i:s'));
        }
    }


    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->getParameter('timestamp');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setTimestamp($value)
    {
        return $this->setParameter('timestamp', $value);
    }


    protected function convertToString()
    {
        foreach ($this->parameters->all() as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $this->parameters->set($key, json_encode($value));
            }
        }
    }


    /**
     * @param array  $params
     * @param string $signType
     *
     * @return string|null
     * @throws InvalidRequestException
     */
    protected function sign($params, $signType)
    {
        $signer = new Signer($params);
        $signer->setIgnores(['sign']);

        $signType = strtoupper($signType);

        if ($signType == 'RSA') {
            $sign = $signer->signWithRSA($this->getPrivateKey());
        } elseif ($signType == 'RSA2') {
            $sign = $signer->signWithRSA($this->getPrivateKey(), OPENSSL_ALGO_SHA256);
        } else {
            throw new InvalidRequestException('The signType is invalid');
        }

        return $sign;
    }


    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPrivateKey($value)
    {
        $this->privateKey = $value;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getSignType()
    {
        return $this->getParameter('sign_type');
    }


    /**
     * @return mixed
     */
    public function getAlipayPublicKey()
    {
        return $this->alipayPublicKey;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAlipayPublicKey($value)
    {
        $this->alipayPublicKey = $value;

        return $this;
    }


    /**
     * @param mixed $data
     *
     * @return mixed|ResponseInterface|StreamInterface
     * @throws NetworkException
     */
    public function sendData($data)
    {
        $method = $this->getRequestMethod();
        $url    = $this->getRequestUrl($data);
        $body   = $this->getRequestBody();

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $response = $this->httpClient->request($method, $url, $headers, $body);

        $payload = $this->decode($response->getBody());

        return $payload;
    }


    /**
     * @return string
     */
    protected function getRequestMethod()
    {
        return 'POST';
    }


    /**
     * @param $data
     *
     * @return string
     */
    protected function getRequestUrl($data)
    {
        $queryParams = $data;

        unset($queryParams['biz_content']);
        ksort($queryParams);

        $url = sprintf('%s?%s', $this->getEndpoint(), http_build_query($queryParams));

        return $url;
    }


    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setEndpoint($value)
    {
        $this->endpoint = $value;

        return $this;
    }


    /**
     * @return string
     */
    protected function getRequestBody()
    {
        $params = [
            'biz_content' => $this->getBizContent()
        ];

        $body = http_build_query($params);

        return $body;
    }


    /**
     * @return mixed
     */
    public function getBizContent()
    {
        return $this->getParameter('biz_content');
    }


    protected function decode($data)
    {
        return json_decode($data, true);
    }


    /**
     * @param null $key
     * @param null $default
     *
     * @return mixed
     */
    public function getBizData($key = null, $default = null)
    {
        if (is_string($this->getBizContent())) {
            $data = json_decode($this->getBizContent(), true);
        } else {
            $data = $this->getBizContent();
        }

        if (is_null($key)) {
            return $data;
        } else {
            return array_get($data, $key, $default);
        }
    }


    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->getParameter('app_id');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAppId($value)
    {
        return $this->setParameter('app_id', $value);
    }


    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->getParameter('format');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setFormat($value)
    {
        return $this->setParameter('format', $value);
    }


    /**
     * @return mixed
     */
    public function getCharset()
    {
        return $this->getParameter('charset');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setCharset($value)
    {
        return $this->setParameter('charset', $value);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSignType($value)
    {
        return $this->setParameter('sign_type', $value);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBizContent($value)
    {
        return $this->setParameter('biz_content', $value);
    }


    /**
     * @return mixed
     */
    public function getAlipaySdk()
    {
        return $this->getParameter('alipay_sdk');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAlipaySdk($value)
    {
        return $this->setParameter('alipay_sdk', $value);
    }


    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setNotifyUrl($value)
    {
        return $this->setParameter('notify_url', $value);
    }


    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->getParameter('return_url');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('return_url', $value);
    }


    /**
     * @return mixed
     */
    public function getEncryptKey()
    {
        return $this->encryptKey;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setEncryptKey($value)
    {
        $this->encryptKey = $value;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->getParameter('version');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setVersion($value)
    {
        return $this->setParameter('version', $value);
    }


    /**
     * @return mixed
     */
    public function getAppAuthToken()
    {
        return $this->getParameter('app_auth_token');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAppAuthToken($value)
    {
        return $this->setParameter('app_auth_token', $value);
    }


    /**
     * @throws InvalidRequestException
     */
    public function validateBizContent()
    {
        $data = $this->getBizContent();

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        foreach (func_get_args() as $key) {
            if (! array_has($data, $key)) {
                throw new InvalidRequestException("The biz_content $key parameter is required");
            }
        }
    }


    /**
     * @throws InvalidRequestException
     */
    public function validateBizContentOne()
    {
        $data = $this->getBizContent();

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        $keys = func_get_args();

        $allEmpty = true;

        foreach ($keys as $key) {
            if (array_has($data, $key)) {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            throw new InvalidRequestException(
                sprintf('The biz_content (%s) parameter must provide one at least', implode(',', $keys))
            );
        }
    }


    protected function filter($data)
    {
        if (! $this->returnable) {
            unset($data['return_url']);
        }

        if (! $this->notifiable) {
            unset($data['notify_url']);
        }
    }


    /**
     * @throws InvalidRequestException
     */
    protected function validateOne()
    {
        $keys = func_get_args();

        if ($keys && is_array($keys[0])) {
            $keys = $keys[0];
        }

        $allEmpty = true;

        foreach ($keys as $key) {
            $value = $this->parameters->get($key);

            if (! empty($value)) {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            throw new InvalidRequestException(
                sprintf('The parameters (%s) must provide one at least', implode(',', $keys))
            );
        }
    }
}
