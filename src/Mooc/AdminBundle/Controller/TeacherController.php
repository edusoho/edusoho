<?php
namespace Mooc\AdminBUndle\Controller;

use Symfony\Component\Httpfoundation\Request;
use Topxia\AdminBundle\Controller\TeacherController as BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class TeacherController extends BaseController
{

    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'roles' => 'ROLE_TEACHER',
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );
        $users = $this->getUserservice()->searchUsers(
            $conditions,
            array('promotedTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userIds = ArrayToolkit::column($users, 'id');
        $user_profiles = $this->getUserservice()->findUserProfilesByIds($userIds);
        foreach ($users as &$user) {
            $user['truename'] = $user_profiles[$user['id']]['truename'];
        }
        unset($user);
        return $this->render('TopxiaAdminBundle:Teacher:index.html.twig', array(
            'users' => $users,
            'paginator' => $paginator,
        ));
    }
}
