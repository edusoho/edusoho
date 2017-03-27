<?php

namespace OAuth2\ServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class VerifyController extends Controller
{
    /**
     * This is called with an access token, details
     * about the access token are then returned.
     * Used for verification purposes.
     *
     * @Route("/verify", name="_verify_token")
     */
    public function verifyAction()
    {
        $server = $this->get('oauth2.server');

        if (!$server->verifyResourceRequest($this->get('oauth2.request'), $this->get('oauth2.response'))) {
            return $server->getResponse();
        }

        $tokenData = $server->getAccessTokenData($this->get('oauth2.request'), $this->get('oauth2.response'));

        return new JsonResponse($tokenData);
    }
}
