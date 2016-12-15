<?php


namespace Biz\File\Convertor;


use Topxia\Service\Util\CloudClient;

abstract class BaseConvertor
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var CloudClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * BaseConvertor constructor.
     *
     * @param CloudClient $client 云Client
     * @param array       $config 转码器配置
     */
    public function __construct(CloudClient $client, array $config)
    {
        $this->client = $client;
        $this->config = $config[$this->name];
    }

    public abstract function saveConvertResult($file, $result);

    /**
     * @param $params
     * @return array
     */
    public abstract function getCovertParams($params);
}