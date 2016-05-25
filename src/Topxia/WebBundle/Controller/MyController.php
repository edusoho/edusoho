<?php
namespace Topxia\WebBundle\Controller;

use Topxia\WebBundle\Util\AvatarAlert;

class MyController extends BaseController
{
    public function avatarAlertAction()
    {
        $user = $this->getCurrentUser();
        return $this->render('TopxiaWebBundle:My:avatar-alert.html.twig', array(
            'avatarAlert' => AvatarAlert::alertInMyCenter($user)
        ));
    }

}
