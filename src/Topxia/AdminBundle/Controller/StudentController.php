<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class StudentController extends BaseController 
{
    public function indexAction (Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'role'=>'ROLE_USER',
            'truename'=>'',
            'number'=>''
        );

        if(!empty($fields)){
            $conditions['truename']=$fields['search_truename'];
            $conditions['number']=$fields['search_number'];
            //$conditions =array_merge($conditions,$fields);
        }
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
        $userMembers=$this->getClassesService()->findClassMembersByUserIds(ArrayToolkit::column($users, 'id'));
        $classes=$this->getClassesService()->findClassesByIds(ArrayToolkit::column($userMembers, 'classId'));
        $userMembers=ArrayToolkit::index($userMembers, 'userId');
        $classes=ArrayToolkit::index($classes, 'id');
        return $this->render('TopxiaAdminBundle:Student:index.html.twig', array(
            'users' => $users,
            'userMembers' =>$userMembers,
            'classes' =>$classes,
            'paginator' => $paginator
        ));
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
    
}