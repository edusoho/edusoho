<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class TokenController extends BaseController
{
    public function getAction(Request $request)
    {
        $this->getOAuth2Server()->addGrantType($this->getOAuth2GrantTypeAuthorizationCode());

        return $this->getOAuth2Server()->handleTokenRequest($this->getOAuth2Request(), $this->getOAuth2Response());
    }

    private function getOAuth2Server()
    {
        return $this->get('oauth2.server');
    }
}