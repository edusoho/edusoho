<?php

namespace AppBundle\Controller\Course;

use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class HomeworkManageController extends BaseController
{
    public function checkAction(Request $request, $id, $answerRecordId)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);

        return $this->forward('AppBundle:HomeworkManage:check', array(
            'request' => $request,
            'answerRecordId' => $answerRecordId,
            'source' => 'course',
            'targetId' => $course['id'],
        ));
    }

    public function checkListAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user = $this->getUser();
        $isTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('course-manage/homework-check/check-list.html.twig', array(
            'courseSet' => $courseSet,
            'course' => $course,
            'isTeacher' => $isTeacher,
        ));
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
