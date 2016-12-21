<?php

namespace OAuth2\ServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class TokenController extends Controller
{
    /**
     * This is called by the client app once the client has obtained
     * an authorization code from the Authorize Controller (@see OAuth2\ServerBundle\Controller\AuthorizeController).
     * returns a JSON-encoded Access Token or a JSON object with
     * "error" and "error_description" properties.
     *
     * @Route("/token", name="_token")
     */
    public function tokenAction()
    {
        $server = $this->get('oauth2.server');

        // Add Grant Types
        $server->addGrantType($this->get('oauth2.grant_type.client_credentials'));
        $server->addGrantType($this->get('oauth2.grant_type.authorization_code'));
        $server->addGrantType($this->get('oauth2.grant_type.refresh_token'));
        $server->addGrantType($this->get('oauth2.grant_type.user_credentials'));

        return $server->handleTokenRequest($this->get('oauth2.request'), $this->get('oauth2.response'));
    }
}
