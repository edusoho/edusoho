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
            'role'=>array('student')
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchClassMemberCount($conditions),
            20
        );
        /**1.获取该班学生的classeMember数据*/
        $classMembers = $this->getUserService()->searchClassMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        /**2.获取学生user数据*/
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($classMembers, 'userId'));
        return $this->render('TopxiaAdminBundle:Student:index.html.twig', array(
            'users' => $users ,
            'classId'=>$classId,
            'paginator' => $paginator
        ));
    }

    public function importAction(Request $request, $classId){

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $numbers=explode(',', $formData['numbers']);
            foreach ($numbers as $number) {
                $user=$this->getUserService()->getUserByNumber($number);
                if(empty($user)){
                    throw $this->createNotFoundException('学号'.$number.'对应的用户不存在！');
                }
                $classMember['classId']=$classId;
                $classMember['userId']=$user['id'];
                $classMember['role']='student';
                $classMember['title']='';
                $classMember['createdTime']=time();
                $this->getUserService()->addClassMember($classMember);
            }
            /**该怎么写？*/
            return $this->render('TopxiaAdminBundle:Student:student-import-modal.html.twig', array(
                'classId' => $classId
            ));
        }

        return $this->render('TopxiaAdminBundle:Student:student-import-modal.html.twig', array(
            'classId' => $classId
        ));
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }
}