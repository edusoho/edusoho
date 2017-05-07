<?php

namespace AppBundle\Controller\OAuth2;

use AppBundle\Controller\BaseController;

class TokenController extends BaseController
{
    public function tokenAction()
    {
        return $this->forward('OAuth2ServerBundle:Token:token');
    }
}
