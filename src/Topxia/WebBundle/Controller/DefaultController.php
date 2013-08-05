<?php

namespace Topxia\WebBundle\Controller;

class DefaultController extends BaseController
{

    public function indexAction ()
    {
        return $this->redirect($this->generateUrl('course_explore'));
    }

}
