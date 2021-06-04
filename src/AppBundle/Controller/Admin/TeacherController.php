<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class TeacherController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->fillOrgCode($conditions);
        $conditions['roles'] = '|ROLE_TEACHER|';
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/teacher/index.html.twig', [
            'users' => $users,
            'paginator' => $paginator,
        ]);
    }

    public function promoteAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $type = $request->query->get('type');

        if ('POST' == $request->getMethod()) {
            $number = $request->request->get('number');
            $user = $this->getUserService()->promoteUser($id, $number);
            $type = $request->request->get('type');

            if ('promoteList' == $type) {
                return $this->render('admin/teacher/teacher-promote-tr.html.twig', ['user' => $user]);
            }

            if ('teacherList' == $type) {
                return $this->render('admin/teacher/tr.html.twig', ['user' => $user]);
            }
        }

        return $this->render('admin/teacher/teacher-promote-modal.html.twig', [
            'user' => $user,
            'type' => $type,
        ]);
    }

    public function promoteListAction(Request $request)
    {
        $user = $this->getUser();
        $fields = $request->query->all();
        $conditions = [
            'roles' => 'ROLE_TEACHER',
            'promoted' => 1,
        ];
        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            ['promotedSeq' => 'ASC', 'promotedTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/teacher/teacher-promote-list.html.twig', [
            'users' => $users,
            'paginator' => $paginator,
        ]);
    }

    public function promoteCancelAction(Request $request, $id)
    {
        $user = $this->getUserService()->cancelPromoteUser($id);

        return $this->render('admin/teacher/tr.html.twig', ['user' => $user]);
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
