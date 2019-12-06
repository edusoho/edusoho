<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\User\Service\UserService;

class TeacherController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->fillOrgCode($conditions);
        $conditions['roles'] = 'ROLE_TEACHER';
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/user/teacher/index.html.twig', array(
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function promoteAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $type = $request->query->get('type');

        if ($request->getMethod() == 'POST') {
            $number = $request->request->get('number');
            $user = $this->getUserService()->promoteUser($id, $number);
            $type = $request->request->get('type');

            if ($type == 'promoteList') {
                return $this->render('admin-v2/user/teacher/teacher-promote-tr.html.twig', array('user' => $user));
            }

            if ($type == 'teacherList') {
                return $this->render('admin-v2/user/teacher/tr.html.twig', array('user' => $user));
            }
        }

        return $this->render('admin-v2/user/teacher/teacher-promote-modal.html.twig', array(
            'user' => $user,
            'type' => $type,
        ));
    }

    public function promoteListAction(Request $request)
    {
        $user = $this->getUser();
        $fields = $request->query->all();
        $conditions = array(
            'roles' => 'ROLE_TEACHER',
            'promoted' => 1,
        );
        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('promotedSeq' => 'ASC', 'promotedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/user/teacher/teacher-promote-list.html.twig', array(
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function promoteCancelAction(Request $request, $id)
    {
        $user = $this->getUserService()->cancelPromoteUser($id);

        return $this->render('admin-v2/user/teacher/tr.html.twig', array('user' => $user));
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
