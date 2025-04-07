<?php

namespace AppBundle\Controller;

use Biz\System\Service\SettingService;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Request;

class WorkflowCallbackController extends BaseController
{
    public function indexAction(Request $request, $workflow)
    {
        $this->auth($request);
        $params = json_decode($request->getContent(), true);
        $biz = $this->getBiz();
        $callback = $biz["workflow.callback.{$workflow}"];
        $callback->execute($params['data']);

        return $this->createJsonResponse([
            'ok' => true,
        ]);
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
