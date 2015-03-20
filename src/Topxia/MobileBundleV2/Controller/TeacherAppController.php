<?php 

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class TeacherAppController extends MobileBaseController{

	public function indexAction(){
        $conditions = array(
            'roles'=>'ROLE_TEACHER',
            'locked'=>0
        );

        // $paginator = new Paginator(
        //     $this->get('request'),
        //     $this->getUserService()->searchUserCount($conditions),
        //     20
        // );

        $teachers = $this->getUserService()->searchUsers(
            $conditions,
            array('promotedTime', 'DESC'),
            0,
            10
        );

        $teachers = $this->filterUsers($teachers);

        $profiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($teachers, 'id'));

		return $this->render('TopxiaMobileBundleV2:Teacher:list.html.twig',array(
            'teachers' => $teachers ,
            'profiles' => $profiles
		));
	}
}