<?php
namespace Topxia\WebBundle\Controller;

class MarkerController extends BaseController
{
    public function manageAction()
    {
        $user = $this->getCurrentUser();
        return $this->render('TopxiaWebBundle:Marker:index.html.twig');
    }

}
