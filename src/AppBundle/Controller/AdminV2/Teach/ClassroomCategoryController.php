<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;

class ClassroomCategoryController extends BaseController
{
    public function indexAction()
    {
        return $this->forward('AppBundle:AdminV2/Teach/Category:embed', array(
            'group' => 'classroom',
            'layout' => 'admin.v2/layout.html.twig',
            'menu' => 'admin_v2_course_category',
        ));
    }
}
