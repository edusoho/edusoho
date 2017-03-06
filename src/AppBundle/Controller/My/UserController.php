<?php

namespace AppBundle\Controller\My;

use AppBundle\Controller\BaseController;
use AppBundle\Util\AvatarAlert;

class UserController extends BaseController
{
    public function avatarAlertAction()
    {
        $user = $this->getCurrentUser();

        return $this->render('my/user/avatar-alert.html.twig', array(
            'avatarAlert' => AvatarAlert::alertInMyCenter($user),
        ));
    }
}
