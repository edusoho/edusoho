<?php
namespace Topxia\Component\Payment;

abstract class Response
{

    protected $userAgent = 'Topxia Payment Client 1.0';

    protected $connectTimeout = 10;

    protected $timeout = 10;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function getRequest($url, $params)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        $url = $url . '?' . http_build_query($params);
        curl_setopt($curl, CURLOPT_URL, $url );

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    abstract public function getPayData();

}