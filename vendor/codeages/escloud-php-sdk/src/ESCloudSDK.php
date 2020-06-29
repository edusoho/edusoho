<?php

namespace ESCloud\SDK;

use ESCloud\SDK\Service\InspectionService;
use Psr\Log\LoggerInterface;
use ESCloud\SDK\HttpClient\ClientInterface;
use ESCloud\SDK\Exception\SDKException;

class ESCloudSDK
{
    protected $options;

    protected $services = array();

    protected $auth;

    protected $logger;

    protected $httpClient;

    /**
     * ESCloudSDK constructor.
     *
     * @param array $options
     * @param LoggerInterface|null $logger
     * @param ClientInterface|null $httpClient
     * @throws InvalidArgumentException
     */
    public function __construct(array $options, LoggerInterface $logger = null, ClientInterface $httpClient = null)
    {
        if (empty($options['access_key'])) {
            throw new \InvalidArgumentException('`access_key` param is missing.');
        }
        if (empty($options['secret_key'])) {
            throw new InvalidArgumentException('`secret_key` param is missing.');
        }

        $this->options = $options;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    /**
     * 获取云资源播放服务
     *
     * @return \ESCloud\SDK\Service\ResourceService
     */
    public function getResourceService()
    {
        return $this->getService('Resource', true);
    }

    /**
     * 获取短信服务
     *
     * @return \ESCloud\SDK\Service\SmsService
     */
    public function getSmsService()
    {
        return $this->getService('Sms');
    }

    /**
     * 获取云资源播放服务
     *
     * @return \ESCloud\SDK\Service\PlayService
     */
    public function getPlayService()
    {
        return $this->getService('Play');
    }

    /**
     * 获取XAPI服务
     *
     * @return \ESCloud\SDK\Service\XAPIService
     */
    public function getXAPIService()
    {
        return $this->getService('XAPI');
    }

    /**
     * 获取分销服务
     *
     * @return \ESCloud\SDK\Service\DrpService
     */
    public function getDrpService()
    {
        return $this->getService('Drp');
    }

    /**
     * @return \ESCloud\SDK\Service\MpService
     */
    public function getMpService()
    {
        return $this->getService('Mp');
    }

    /**
     * @return \ESCloud\SDK\Service\ESopService
     */
    public function getESopService()
    {
        return $this->getService('ESop');
    }

    /**
     * @return \ESCloud\SDK\Service\AiService
     */
    public function getAiService()
    {
        return $this->getService('Ai');
    }

    /**
     * @return \ESCloud\SDK\Service\PushService
     */
    public function getPushService()
    {
        return $this->getService('Push');
    }

    /**
     * @return \ESCloud\SDK\Service\NotificationService
     */
    public function getNotificationService()
    {
        return $this->getService('Notification');
    }

    /**
     * @return \ESCloud\SDK\Service\WeChatService
     */
    public function getWeChatService()
    {
        return $this->getService('WeChat');
    }

    /**
     * @return InspectionService
     */
    public function getInspectionService()
    {
        return $this->getService('Inspection', true);
    }

    /**
     * @return \ESCloud\SDK\Service\MobileService
     */
    public function getMobileService()
    {
        return $this->getService('Mobile');
    }

    /**
     * 根据服务名获得服务实例
     *
     * @param string $name 服务名
     *
     * @return mixed 服务实例
     */
    protected function getService($name, $useJwt = false)
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        $lowerName = strtolower($name);
        $options = empty($this->options['service'][$lowerName]) ? array() : $this->options['service'][$lowerName];

        $class = __NAMESPACE__.'\\Service\\'.$name.'Service';
        $auth = new Auth($this->options['access_key'], $this->options['secret_key'],  $useJwt);
        $this->services[$name] = new $class($auth, $options, $this->logger, $this->httpClient);

        return $this->services[$name];
    }
}
