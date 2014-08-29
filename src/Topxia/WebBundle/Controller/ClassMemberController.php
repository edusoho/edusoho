<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ClassMemberController extends BaseController
{
	public function listAction(Request $request,$classId){
		$class = $this->getClassService()->getClass($classId);
        if (empty($class)) {
            throw $this->createNotFoundException("班级不存在，或已删除。");
        }
        $conditions = array(
            'classId'=>$class['id'],
            'gradeId'=>$class['gradeId'],
            'term'=>$class['term']
        );
        /**本班所有任课老师*/
        $courses=$this->getCourseService()->searchCourses($conditions,null, 0, PHP_INT_MAX);
        $teacherIds=ArrayToolkit::column($courses, 'teacherIds');
        $userIds=array();
        foreach ($teacherIds as $teacherId) {
        	$userIds[]=$teacherId[0];
        }
        $teachers=$this->getUserService()->findUsersByIds($userIds);
        $teaherProfiles=$this->getUserService()->findUserProfilesByIds($userIds);
        /**本班所有学生*/
        $conditions = array(
            'classId'=>$classId,
            'roles'=>array('student')
        );
        $studentMembers = $this->getClassMemberService()->searchClassMembers(
            $conditions,
            array('createdTime', 'DESC'),
            0,
            PHP_INT_MAX
        );
        $students=$this->getUserService()->findUsersByIds(ArrayToolkit::column($studentMembers, 'userId'));
		return $this->render("TopxiaWebBundle:ClassMember:member-show.html.twig",array(
			'class'=>$class,
			'classNav'=>'members',
			'courses'=>$courses,
			'teachers'=>$teachers,
			'teacherProfiles'=>$teaherProfiles,
			'students'=>$students
		));
	}

	protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getClassMemberService(){
        return $this->getServiceKernel()->createService('Classes.ClassMemberService');
    }
}