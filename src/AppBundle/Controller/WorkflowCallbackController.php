<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class WorkflowCallbackController extends BaseController
{
    public function indexAction(Request $request, $workflow)
    {
        $this->authByToken($request);
        $params = json_decode($request->getContent(), true);
        $biz = $this->getBiz();
        $callback = $biz["workflow.callback.{$workflow}"];
        try {
            $callback->execute($params['outputs']);
        } catch (\Exception $e) {
            return $this->createJsonResponse([
                'ok' => false,
                'error' => [
                    'code' => 'UNKNOWN_ERROR',
                    'message' => $e->getMessage(),
                ],
            ]);
        }

        return $this->createJsonResponse([
            'ok' => true,
        ]);
    }
}
