<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ClassMemberController extends ClassBaseController
{
	public function listAction(Request $request,$classId){
        $class = $this->tryViewClass($classId);
        $headTeacher = $this->getUserService()->getUser($class['headTeacherId']);
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

        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds'] ? : array());
        }

        $teachers=$this->getUserService()->findUsersByIds($userIds);
        /**本班所有学生*/
        $conditions = array(
            'classId'=>$classId,
            'roles'=>array('STUDENT')
        );
        $studentMembers = $this->getClassService()->searchClassMembers(
            $conditions,
            array('createdTime', 'DESC'),
            0,
            PHP_INT_MAX
        );
        $students=$this->getUserService()->findUsersByIds(ArrayToolkit::column($studentMembers, 'userId'));
        //@todo member-list.html.twig
		return $this->render("TopxiaWebBundle:ClassMember:member-show.html.twig",array(
			'class'=>$class,
			'classNav'=>'members',
			'courses'=>$courses,
            'headTeacher'=>$headTeacher,
			'teachers'=>$teachers,
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

}