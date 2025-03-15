<?php

namespace AgentBundle\Client;

use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification;
use Firebase\JWT\JWT;
use MarketingMallBundle\Exception\MarketingMallApiException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class AgentApi
{
    private static $client;

    private static $logger;

    private static $headers = [];

    private static $timestamp = null;

    private static $accessKey = '';

    private static $secretKey = '';

    private static $api = '/v1/';

    public function __construct($storage)
    {
        $mallUrl = ServiceKernel::instance()->getParameter('marketing_mall_url');
        $setting = $this->getSettingService()->get('marketing_mall', []);
        self::$accessKey = $setting['access_key'] ?? '';
        self::$secretKey = $setting['secret_key'] ?? '';
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Authorization: '.$this->makeToken();
        self::$headers = $headers;
        self::$timestamp = time();
        $config = [
            'access_key' => $storage['cloud_access_key'] ?? '',
            'secret_key' => $storage['cloud_secret_key'] ?? '',
            'endpoint' => empty($storage['mall_private_server']) ? $mallUrl : rtrim($storage['mall_private_server'], '/'),
        ];
        $logger = self::getLogger();
        $spec = new JsonHmacSpecification('sha1');
        self::$client = new RestApiClient($config, $spec, null, $logger);
    }

    public function enableAiService($params)
    {
        $params['name'] = 'teacher';
        $this->post(self::$api.'feature/enable', $params);
    }

    public function disableAiService($params)
    {
        $params['name'] = 'teacher';
        $this->post(self::$api.'feature/disable', $params);
    }

    public function disableLearnAssistant($params)
    {
        $params['name'] = 'teacher';
        $this->post(self::$api.'feature/disable', $params);
    }

    /**
     * 创建知识库
     * @param $course
     * @return void
     */
    public function createDataset($params)
    {
        $course = $params['course'];
        $aiStudyConfig = $params['aiStudyConfig'];
        $params = [
            'no' => $course['id'],
            'name' => $course['courseSetTitle'],
            'domainId' => $aiStudyConfig['majorId'],
            'autoIndex' => $aiStudyConfig['isDiagnosisActive'] == 1,
        ];
        $this->post(self::$api.'dataset/create', $params);
    }

    private function get($uri, array $params = [])
    {
        $params['code'] = self::$accessKey;

        try {
            $response = self::$client->get($uri, $params, self::$headers);
        } catch (\RuntimeException $e) {
            throw new MarketingMallApiException('营销商城服务异常，请联系管理员('.$uri.')');
        }
        if (empty($response)) {
            $this->getLogger()->warn('market-mall-post', ['uri' => $uri, 'params' => $params]);
        } else {
            $this->getLogger()->debug('market-mall-post', ['uri' => $uri, 'params' => $params, 'response' => $response]);
        }

        return $response;
    }

    private function post($uri, array $params = [])
    {
        try {
            $response = self::$client->post($uri, $params, self::$headers);
        } catch (\RuntimeException $e) {
            throw new MarketingMallApiException('营销商城服务异常，请联系管理员('.$uri.')');
        }

        if (empty($response)) {
            $this->getLogger()->warn('market-mall-post', ['uri' => $uri, 'params' => $params]);
        } else {
            $this->getLogger()->debug('market-mall-post', ['uri' => $uri, 'params' => $params, 'response' => $response]);
        }

        return $response;
    }

    private function makeToken()
    {
        return self::$accessKey.':'.JWT::encode(['exp' => time() + 1000 * 3600 * 24, 'access_key' => self::$accessKey], self::$secretKey);
    }

    private function getLogger()
    {
        if (self::$logger) {
            return self::$logger;
        }
        $logger = new Logger('marketing-mall');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/marketing-mall.log', Logger::DEBUG));

        self::$logger = $logger;

        return $logger;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
