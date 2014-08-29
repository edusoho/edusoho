<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Topxia\Common\SimpleValidator;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Topxia\Common\ConvertIpToolkit;

class StudentController extends BaseController 
{

    public function indexAction (Request $request,$classId)
    {   
        $conditions = array(
            'classId'=>$classId,
            'roles'=>array('STUDENT')
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassMemberService()->searchClassMemberCount($conditions),
            20
        );
        /**1.获取该班学生的classeMember数据*/
        $classMembers = $this->getClassMemberService()->searchClassMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        /**2.获取学生user数据*/
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($classMembers, 'userId'));
        $userProfiles=$this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($classMembers, 'userId'));
        return $this->render('TopxiaAdminBundle:Student:student-list.html.twig', array(
            'users' => $users ,
            'userProfiles'=>$userProfiles,
            'classId'=>$classId,
            'paginator' => $paginator
        ));
    }

    public function importAction(Request $request, $classId){
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $numbers=explode(',', $formData['numbers']);
            $users=array();
            foreach ($numbers as $number) {
                $user=$this->getUserService()->getUserByNumber($number);
                if(empty($user)){
                    return $this->createJsonResponse('学号'.$number.'对应的用户不存在！');
                }
                $conditions=array(
                    'userId'=>$user['id'],
                    'roles'=>array('STUDENT')
                );
                $classMembers=$this->getClassMemberService()->searchClassMembers($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);
                if(count($classMembers)>0 && $classMembers[0]['classId']!=$classId){
                    return $this->createJsonResponse('学号'.$number.'对应的用户已经属于其他班级！');
                }
                if(count($classMembers)>0 && $classMembers[0]['classId']==$classId){
                    continue;
                }
                $users[]=$user;
            }
            foreach ($users as $user) {
                $classMember['classId']=$classId;
                $classMember['userId']=$user['id'];
                $classMember['role']='student';
                $classMember['title']='';
                $classMember['createdTime']=time();
                $this->getClassMemberService()->addClassMember($classMember);
            }
            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaAdminBundle:Student:student-import-modal.html.twig', array(
            'classId' => $classId
        ));
    }
    
    public function removeAction(Request $request, $userId){
        $this->getClassMemberService()->deleteClassMemberByUserId($userId);
        return $this->createJsonResponse(true);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getClassMemberService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassMemberService');
    }

}