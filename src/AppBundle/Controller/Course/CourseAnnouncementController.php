<?php
namespace AppBundle\Controller\Course;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class CourseAnnouncementController extends BaseController
{
    public function createAction(Request $request, $targetId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);

        //TODO:这里需要根据用户所拥有的计划的权限展示
        $plans = $this->getCourseService()->findCoursesByCourseSetId($course['courseSetId']);

        return $this->render('course/announcement/announcement-write-modal.html.twig', array(
            'announcement' => array('id' => '', 'content' => ''),
            'targetObject' => $course,
            'targetType'   => 'course',
            'targetId'     => $targetId,
            'plans'        => $plans
        ));
    }

    /*public function showAction(Request $request, $announcementId)
    {
    }
     */

    public function editAction(Request $request, $targetId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);

        //TODO:这里需要根据用户所拥有的计划的权限展示
        $plans = $this->getCourseService()->findCoursesByCourseSetId($course['courseSetId']);

        $announcement = $this->getAnnouncementService()->getAnnouncement($id);

        return $this->render('announcement/announcement-show-modal.html.twig', array(
            'announcement' => $announcement,
            'targetObject' => $course
        ));
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
