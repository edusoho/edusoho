<?php

namespace AgentBundle\Client;

use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification;
use Firebase\JWT\JWT;
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

    private static $url = 'https://test-ai-service.edusoho.cn/v1/';

    public function __construct($storage)
    {
        self::$accessKey = $storage['cloud_access_key'] ?? '';
        self::$secretKey = $storage['cloud_secret_key'] ?? '';
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Authorization: Bearer '.$this->makeToken();
        self::$headers = $headers;
        self::$timestamp = time();
        $logger = self::getLogger();
        $spec = new JsonHmacSpecification('sha1');
        self::$client = new RestApiClient([], $spec, null, $logger);
    }

    public function enableAiService($params)
    {
        $params['name'] = 'teacher';
        $this->post(self::$url.'feature/enable', $params);
    }

    public function disableAiService($params)
    {
        $params['name'] = 'teacher';
        $this->post(self::$url.'feature/disable', $params);
    }

    public function disableLearnAssistant($params)
    {
        $params['name'] = 'teacher';
        $this->post(self::$url.'feature/disable', $params);
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
        return $this->post(self::$url.'dataset/create', $params);
    }

    private function get($uri, array $params = [])
    {
        $params['code'] = self::$accessKey;

        try {
            $response = self::$client->get($uri, $params, self::$headers);
        } catch (\RuntimeException $e) {
            $this->getLogger()->error('agent-post', ['uri' => $uri, 'params' => $params, 'response' => $e]);
            return [];
        }
        if (empty($response)) {
            $this->getLogger()->warn('agent-post', ['uri' => $uri, 'params' => $params]);
        } else {
            $this->getLogger()->debug('agent-post', ['uri' => $uri, 'params' => $params, 'response' => $response]);
        }

        return $response;
    }

    private function post($uri, array $params = [])
    {
        try {
            $response = self::$client->post($uri, $params, self::$headers);
        } catch (\RuntimeException $e) {
            $this->getLogger()->error('agent-post', ['uri' => $uri, 'params' => $params, 'response' => $e]);
            return [];
        }

        if (empty($response)) {
            $this->getLogger()->warn('agent-post', ['uri' => $uri, 'params' => $params]);
        } else {
            $this->getLogger()->debug('agent-post', ['uri' => $uri, 'params' => $params, 'response' => $response]);
        }

        return $response;
    }

    private function makeToken()
    {
        $payload = [
            "iss" => "AI",
            "exp" => time() + 1000 * 3600 * 24 // 过期时间戳（需确保大于当前时间）
        ];
        return JWT::encode(
            $payload,
            self::$secretKey,
            'HS256',
            self::$accessKey,
        );
    }

    private function getLogger()
    {
        if (self::$logger) {
            return self::$logger;
        }
        $logger = new Logger('agent');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/agent.log', Logger::DEBUG));

        self::$logger = $logger;

        return $logger;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
