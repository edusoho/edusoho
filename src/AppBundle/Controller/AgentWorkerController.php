<?php

namespace AppBundle\Controller;

use AgentBundle\Workflow\Workflow;
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
        $worker = $this->getWorkflowWorker($params['workflow']);
        if (empty($worker)) {
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
        try {
            $result = $worker->execute($params['inputs']);
        } catch (\Exception $e) {
            return $this->createJsonResponse([
                'ok' => false,
                'error' => [
                    'code' => 'UNKNOWN_ERROR',
                    'message' => $e->getMessage(),
                ],
            ]);
        }

        return $this->createJsonResponse($result);
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
     * @param $workflow
     * @return Workflow
     */
    private function getWorkflowWorker($workflow)
    {
        $biz = $this->getBiz();

        return $biz["agent.workflow.{$workflow}"] ?? null;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
