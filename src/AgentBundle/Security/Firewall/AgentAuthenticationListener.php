<?php

namespace AgentBundle\Security\Firewall;

use ApiBundle\Security\Firewall\BaseAuthenticationListener;
use Firebase\JWT\JWT;
use Http\Discovery\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class AgentAuthenticationListener extends BaseAuthenticationListener
{
    const AGENT_AUTH_HEADER = 'Authorization';

    public function handle(Request $request)
    {
        $authorization = $request->headers->get(self::AGENT_AUTH_HEADER);
        if (false === strpos($authorization, 'Bearer')) {
            return;
        }
        if (!empty($authorization)) {
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
            throw new NotFoundException('token error！');
        }
        $user = $this->getUserService()->getUser($payload->sub);
        if (empty($user)) {
            throw new NotFoundException('user not found！');
        }
        $token = $this->createTokenFromRequest($request, $user['id']);
        $this->getTokenStorage()->setToken($token);
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
