<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Security\Authentication\WebLibAuthenticationKeyProvider;
use Codeages\Weblib\Auth\Authentication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class WebLibAuthenticationListener extends BaseAuthenticationListener
{
    public function handle(Request $request)
    {
        if (null !== $this->getTokenStorage()->getToken()) {
            return;
        }

        $keyProvider = new WebLibAuthenticationKeyProvider($this->getBiz());
        $authentication = new Authentication($keyProvider);
        try {
            $header = $authentication->getTokenHeader($request);
            $header = explode(' ', $header);
            if (2 !== count($header)) {
                return;
            }

            if (!in_array($header[0], array('Signature', 'Secret'))) {
                return;
            }
        } catch (\InvalidArgumentException $e) {
            return;
        }

        try {
            $authentication->auth($request);
        } catch (\Exception $e) {
            $biz = $this->getBiz();
            $logger = $biz['logger'];
            $logger->error($e);
            throw new UnauthorizedHttpException('WebLib', 'authorized error');
        }

        $systemUser = $this->getUserService()->getUserByType('system');
        $token = $this->createTokenFromRequest($request, $systemUser['id']);
        $this->getTokenStorage()->setToken($token);
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
