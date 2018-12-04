<?php

namespace ApiBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;

class OAuth2AuthenticationListener extends BaseAuthenticationListener
{
    public function handle(Request $request)
    {
        if (null !== $this->getTokenStorage()->getToken()) {
            return;
        }

        $oauthRequest = \OAuth2\HttpFoundationBridge\Request::createFromRequest($request);
        if ($this->isOAuth2VerifyPass($oauthRequest)) {
            $tokenData = $this->getOAuth2Sever()->getAccessTokenData($oauthRequest);
            $userId = $tokenData['user_id'];
            $token = $this->createTokenFromRequest($request, $userId);
            $this->getTokenStorage()->setToken($token);
        }
    }

    private function isOAuth2VerifyPass($oauthRequest)
    {
        return $this->getOAuth2Sever()->verifyResourceRequest($oauthRequest);
    }

    private function getOAuth2Sever()
    {
        return $this->container->get('oauth2.server');
    }
}
