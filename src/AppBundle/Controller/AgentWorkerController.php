<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Request;

class AgentWorkerController extends BaseController
{
    public function indexAction(Request $request)
    {
        if (!$request->isMethod('post')) {
            return $this->createJsonResponse(
                [
                    'ok' => false,
                    'error' => [
                        'code' => 'HTTP_METHOD_NOT_ALLOWED',
                        'message' => '',
                    ],
                ]
            );
        }
        $this->auth($request);
        if (!$this->getCurrentUser()->isLogin()) {
            return $this->createJsonResponse(
                [
                    'ok' => false,
                    'error' => [
                        'code' => 'AUTH_REQUIRED',
                        'message' => '',
                    ],
                ]
            );
        }
        $params = json_decode($request->getContent(), true);
        if (!ArrayToolkit::requireds($params, ['workflow', 'inputs'])) {
            return $this->createJsonResponse(
                [
                    'ok' => false,
                    'error' => [
                        'code' => 'PARAMS_LACK',
                        'message' => '参数缺失',
                    ],
                ]
            );
        }
        $biz = $this->getBiz();
        if (empty($biz["agent.workflow.{$params['workflow']}"])) {
            return $this->createJsonResponse(
                [
                    'ok' => false,
                    'error' => [
                        'code' => 'UNKNOWN_WORKFLOW',
                        'message' => '未知工作流',
                    ],
                ]
            );
        }
        $workflow = $biz["agent.workflow.{$params['workflow']}"];

        return $this->createJsonResponse($workflow->execute($params['inputs']));
    }

    private function auth(Request $request)
    {
        $authorization = $request->headers->get('Authorization');
        if (!empty($authorization) && (false !== strpos($authorization, 'Bearer '))) {
            $token = str_replace('Bearer ', '', $authorization);
        } else {
            $token = $request->query->get('token');
        }
        if (empty($token)) {
            return;
        }
        $storage = $this->getSettingService()->get('storage', []);
        try {
            $payload = JWT::decode($token, [$storage['cloud_access_key'] => $storage['cloud_secret_key']], ['HS256']);
        } catch (\RuntimeException $e) {
            return;
        }
        $user = $this->getUserService()->getUser($payload->sub);
        if (empty($user)) {
            return;
        }
        $this->authenticateUser($user);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
