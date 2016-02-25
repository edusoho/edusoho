<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class TeacherController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields     = $request->query->all();
        $conditions = array(
            'roles' => 'ROLE_TEACHER'
        );

        if (empty($fields)) {
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

        $conditions = ArrayToolkit::parts($conditions, array('roles', 'nickname'));

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:Teacher:index.html.twig', array(
            'users'     => $users,
            'paginator' => $paginator
        ));
    }

    public function promoteAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $type = $request->query->get('type');

        if ($request->getMethod() == 'POST') {
            $number = $request->request->get('number');
            $user   = $this->getUserService()->promoteUser($id, $number);
            $type   = $request->request->get('type');

            if ($type == 'promoteList') {
                return $this->render('TopxiaAdminBundle:Teacher:teacher-promote-tr.html.twig', array('user' => $user));
            }

            if ($type == 'teacherList') {
                return $this->render('TopxiaAdminBundle:Teacher:tr.html.twig', array('user' => $user));
            }
        }

        return $this->render('TopxiaAdminBundle:Teacher:teacher-promote-modal.html.twig', array(
            'user' => $user,
            'type' => $type
        ));
    }

    public function promoteListAction(Request $request)
    {
        $fields     = $request->query->all();
        $conditions = array(
            'roles'    => 'ROLE_TEACHER',
            'promoted' => 1
        );

        if (empty($fields)) {
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

        $conditions = ArrayToolkit::parts($conditions, array('roles', 'promoted', 'nickname'));

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('promotedSeq', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $promotedSeq  = ArrayToolkit::column($users, 'promotedSeq');
        $promotedTime = ArrayToolkit::column($users, 'promotedTime');
        array_multisort($promotedSeq, SORT_ASC, $promotedTime, SORT_DESC, $users);

        return $this->render('TopxiaAdminBundle:Teacher:teacher-promote-list.html.twig', array(
            'users'     => $users,
            'paginator' => $paginator
        ));
    }

    public function promoteCancelAction(Request $request, $id)
    {
        $user = $this->getUserService()->cancelPromoteUser($id);

        return $this->render('TopxiaAdminBundle:Teacher:tr.html.twig', array('user' => $user));
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
