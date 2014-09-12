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
        $class=$this->getClassService()->getClass($classId);
        $conditions = array(
            'classId'=>$classId,
            'roles'=>array('STUDENT')
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassService()->searchClassMemberCount($conditions),
            20
        );
        $classMembers = $this->getClassService()->searchClassMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($classMembers, 'userId'));
        return $this->render('TopxiaAdminBundle:Student:student-list.html.twig', array(
            'users' => $users ,
            'class'=>$class,
            'paginator' => $paginator
        ));
    }

    public function importAction(Request $request, $classId){
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $formData['numbers'] = str_replace('，', ',', $formData['numbers']); 
            $formData['numbers'] = str_replace("\n", ',', $formData['numbers']); 
            $formData['numbers'] = str_replace("\r", ',', $formData['numbers']); 
            $formData['numbers'] = str_replace(' ', ',', $formData['numbers']); 
            $numbers = explode(',', $formData['numbers']);
            $userIds=array();
            foreach ($numbers as $number) {
                $number=trim($number);
                if($number==''){
                    continue;
                }
                $user=$this->getUserService()->getUserByNumber($number);
                if(empty($user) || in_array('ROLE_TEACHER', $user['roles'])){
                    return $this->createJsonResponse('学号'.$number.'对应的用户不存在！');
                }
                $studentMember=$this->getClassService()->getStudentMemberByUserIdAndClassId($user['id'],$classId);
                
                if(!empty($studentMember) && $studentMember['classId']!=$classId){
                    return $this->createJsonResponse($user['truename'].'('.'学号'.$number.')'.'对应的用户已经属于其他班级！');
                }
                if(!empty($studentMember) && $studentMember['classId']==$classId){
                    continue;
                }

                $userIds[]=$user['id'];
            }
            $this->getClassService()->importStudents($classId,$userIds);
            return $this->createJsonResponse(true);
        }
        return $this->render('TopxiaAdminBundle:Student:student-import-modal.html.twig', array(
            'classId' => $classId
        ));
    }
    
    public function removeAction(Request $request, $userId ,$classId){
        $this->getClassService()->deleteClassMemberByUserId($userId);
        $this->getClassService()->updateClassStudentNum(-1,$classId);
        return $this->createJsonResponse(true);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getClassService(){
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

}