<?php

namespace Biz\Question;

use Biz\Common\JsonLogger;
use Biz\System\Service\SettingService;
use Codeages\RestApiClient\HttpRequest\CurlHttpRequest;
use Firebase\JWT\JWT;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class QuestionParseClient
{
    private $api = 'http://question-parse-service.labs-dev.edusoho.cn/api-open';

    private $request;

    private $token;

    public function __construct()
    {
        $this->request = new CurlHttpRequest([], $this->getLogger(), true);
        $this->initToken();
    }

    public function parse($filename)
    {
        return $this->post('/parse', ['file' => new \CURLFile($filename)], ['Content-Type: multipart/form-data']);
    }

    public function getJob($nos)
    {
        $body = $this->get('/job', ['nos' => $nos]);

        return json_decode($body, true);
    }

    private function post($uri, $body, array $headers)
    {
        $headers[] = "Authorization: Bearer $this->token";

        return $this->request->request('POST', $this->api.$uri, $body, $headers);
    }

    private function get($uri, $params)
    {
        $uri = $uri . (strpos($uri, '?') ? '&' : '?') . http_build_query($params);

        return $this->request->request('GET', $this->api.$uri, $params, ["Authorization: Bearer $this->token"]);
    }

    private function initToken()
    {
        $storage = $this->getSettingService()->get('storage');
        $payload = [
            'iss' => 'QuestionParseService',
            'exp' => time() + 3000,
        ];
        $storage['cloud_access_key'] = '9KZsGvJaLhSfD4YDjRyLiaRXtwhGejv2';
        $storage['cloud_secret_key'] = '8ovd4TvFCCtKNCtEJLdTwTUUMeQoUrjB';
        $this->token = JWT::encode($payload, $storage['cloud_secret_key'], 'HS256', $storage['cloud_access_key']);
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    private function getLogger()
    {
        $biz = $this->getBiz();
        $streamHandler = new StreamHandler($biz['log_directory'].'/question-parse-api.log', Logger::DEBUG);

        return new JsonLogger('QuestionParseAPI', $streamHandler);
    }

    private function getBiz()
    {
        return ServiceKernel::instance()->getBiz();
    }
}
