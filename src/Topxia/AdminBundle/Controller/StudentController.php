<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $classStudents=array();
        $classes=array();
        if(isset($fields['search_truename'])){
            $conditions['truename']=$fields['search_truename'];
            $conditions['number']=$fields['search_number'];
            if(!empty($fields['class_id'])){
                $classStudents=$this->getClassesService()->findClassStudentMembers($fields['class_id']);
                $ids=ArrayToolkit::column($classStudents, 'userId');
                $conditions['ids']=empty($ids) ? array(0) : $ids;
            }
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

        $classStudents=$this->getClassesService()->findClassMembersByUserIds(ArrayToolkit::column($users, 'id'));
        $classes=$this->getClassesService()->findClassesByIds(ArrayToolkit::column($classStudents, 'classId'));

        $classStudents=ArrayToolkit::index($classStudents, 'userId');
        $classes=ArrayToolkit::index($classes, 'id');
        return $this->render('TopxiaAdminBundle:Student:index.html.twig', array(
            'users' => $users,
            'classStudents' =>$classStudents,
            'classes' =>$classes,
            'paginator' => $paginator
        ));
    }

    public function matchAction(Request $request)
    {
        $matchStr = $request->query->get('q');

        if(!!preg_match('/^[\x{4e00}-\x{9fa5}]{1,5}$/u', $matchStr)){
            $conditions = array(
                'role' => 'ROLE_USER',
                'truename'=> $matchStr 
            );
        }else{
            $conditions = array(
                'role' => 'ROLE_USER',
                'numberLike'=> $matchStr 
            );
        }
        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id','ASC'),
            0,
            10
        );

        $response = array();
        foreach ($users as $user) {
            $temp = array();
            $temp['id'] = $user['id'];
            $temp['name'] = $user['truename'].'('.$user['number'].')';
            $response[] = $temp;
        }
        return  $this->createJsonResponse($response); 
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
}