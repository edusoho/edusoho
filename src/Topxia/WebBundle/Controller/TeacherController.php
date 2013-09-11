<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class TeacherController extends BaseController
{
    public function indexAction()
    {

        $conditions = array(
            'roles'=>'ROLE_TEACHER',
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $teachers = $this->getUserService()->searchUsers(
            $conditions,
            array('promotedTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $profiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($teachers, 'id'));

        return $this->render('TopxiaWebBundle:Teacher:index.html.twig', array(
            'teachers' => $teachers ,
            'profiles' => $profiles,
            'paginator' => $paginator
        ));
    }
}