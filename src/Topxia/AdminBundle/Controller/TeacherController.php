<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class TeacherController extends BaseController {

    public function indexAction (Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'roles'=>'ROLE_TEACHER',
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('promotedTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:Teacher:index.html.twig', array(
            'users' => $users ,
            'paginator' => $paginator
        ));
    }

    public function promoteAction(Request $request, $id)
    {
        $this->getUserService()->promoteUser($id);
        return $this->createJsonResponse(true);
    }

    public function promoteCancelAction(Request $request, $id)
    {
        $this->getUserService()->cancelPromoteUser($id);
        return $this->createJsonResponse(true);
    }


}