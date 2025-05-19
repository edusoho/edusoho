<?php

namespace AgentBundle\Controller;

use AgentBundle\Workflow\Workflow;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
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
                        'message' => '只允许POST',
                    ],
                ]
            );
        }
        $this->authByToken($request);
        if (!$this->getCurrentUser()->isLogin()) {
            return $this->createJsonResponse(
                [
                    'ok' => false,
                    'error' => [
                        'code' => 'AUTH_REQUIRED',
                        'message' => '认证失败',
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

    /**
     * @param $workflow
     * @return Workflow
     */
    private function getWorkflowWorker($workflow)
    {
        $biz = $this->getBiz();

        return $biz["agent.workflow.{$workflow}"] ?? null;
    }
}
