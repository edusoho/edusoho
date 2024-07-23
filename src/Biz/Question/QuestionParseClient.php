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
    private $api = 'question-parse-service.edusoho.net';

    private $request;

    private $token;

    public function __construct()
    {
        $this->request = new CurlHttpRequest([], $this->getLogger(), true);
        $this->initApi();
        $this->initToken();
    }

    public function parse($filename)
    {
        return $this->post('/api-open/parse', ['file' => new \CURLFile($filename)], ['Content-Type: multipart/form-data']);
    }

    public function getJob($nos)
    {
        $body = $this->get('/api-open/job', ['nos' => $nos]);

        return json_decode($body, true);
    }

    public function convertLatex2Img($exps)
    {
        $body = $this->post('/api-open/latex2img', json_encode(['exps' => $exps], JSON_UNESCAPED_SLASHES), ['Content-Type: application/json']);

        return json_decode($body, true);
    }

    public function getTemplateFileDownloadUrl($type, $ssl)
    {
        $type = in_array($type, ['docx-full', 'docx-simple', 'xlsx']) ? $type : 'docx-full';
        $protocol = $ssl ? 'https://' : 'http://';
        $api = 0 === strpos($this->api, 'http') ? $this->api : $protocol.$this->api;

        return "$api/api-public/templateFile?type={$type}";
    }

    private function post($uri, $body, array $headers)
    {
        $headers[] = "Authorization: Bearer $this->token";

        return $this->request->request('POST', $this->api.$uri, $body, $headers);
    }

    private function get($uri, $params)
    {
        $uri = $uri.(strpos($uri, '?') ? '&' : '?').http_build_query($params);

        return $this->request->request('GET', $this->api.$uri, $params, ["Authorization: Bearer $this->token"]);
    }

    private function initApi()
    {
        $storage = $this->getSettingService()->get('storage');
        if (!empty($storage['question_parse_api_server'])) {
            $this->api = $storage['question_parse_api_server'];
        }
    }

    private function initToken()
    {
        $storage = $this->getSettingService()->get('storage');
        $payload = [
            'iss' => 'QuestionParseService',
            'exp' => time() + 300,
        ];
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
