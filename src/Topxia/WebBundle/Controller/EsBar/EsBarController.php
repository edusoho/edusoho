<?php
/**
 * author: retamia
 * Time: 15-7-7 上午11:12
 * description: 
 */
namespace Topxia\WebBundle\Controller\EsBar;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class EsBarController extends BaseController{
    public function liveNotifyAction(Request $request,$userId)
    {
        $filters['type'] = 'live';
        $liveCourses = $this->getCourseService()->findUserLeaningCourses($userId,0,100,$filters);
        $liveLessons = array();
        if(!empty($liveCourses)){
            $conditions = array(
                'status' => 'published',
                'courseIds' => ArrayToolkit::column($liveCourses,'id'),
                'type' => 'live',
                'startTimeGreaterThan' => time()
            );
            $sort = array(
                'startTime','ASC'
            );
            $liveLessons = $this->getCourseService()->searchLessons($conditions,$sort,0,2);
        }
        return $this->render("TopxiaWebBundle:EsBar:live-notify.html.twig", array(
            'liveLessons' => $liveLessons,
        ));
    }

    public function studyPlanClassroomAction(Request $request,$userId)
    {
        $memberConditions = array(
            'userId' => $userId,
            'locked' => 0,
        );
        $sort = array('createdTime','DESC');
        $classrooms = array();
        $classroomIds = ArrayToolkit::column($this->getClassroomService()->searchMembers($memberConditions,$sort,0,5),'classroomId');
        if(!empty($classroomIds)){
            $classroomConditions = array(
                'classroomIds' => $classroomIds
            );
            $classrooms = $this->getClassroomService()->searchClassrooms($classroomConditions,$sort,0,5);
        }
        return $this->render("TopxiaWebBundle:EsBar:study-plan-classroom.html.twig", array(
            'classrooms' => $classrooms
        ));
    }

    public function studyPlanLessonAction(Request $request,$classroomId)
    {
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($courses,'id');
        $user = $this->getCurrentUser();
        $learnedConditions = array(
            'userId' => $user->id,
            'status' => 'finished',
            'courseIds' => $courseIds
        );
        $sort = array( 'finishedTime','ASC');
        $learnedIds = ArrayToolkit::column($this->getCourseService()->searchLearns($learnedConditions,$sort,0,1000),'lessonId');

        $notLearnedConditions = array(
            'status' => 'published',
            'courseIds' => $courseIds,
            'notLearnedIds' => $learnedIds
        );
        $sort = array(
            'createdTime','DESC'
        );
        $lessons = $this->getCourseService()->searchLessons($notLearnedConditions,$sort,0,5);

        return $this->render("TopxiaWebBundle:EsBar:study-plan-lesson.html.twig", array(
            'lessons' => $lessons
        ));
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}