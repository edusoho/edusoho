<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class HomeworkController extends BaseController
{

    public function teachingListAction(Request $request)
    {   
        $status = $request->query->get('status', 'unchecked');

        $currentUser = $this->getCurrentUser();
        if (empty($currentUser)) {
            throw $this->createServiceException('用户不存在或者尚未登录，请先登录');
        }
        $homeworks = ArrayToolkit::index($this->getHomeworkService()->findHomeworksByCreatedUserId($currentUser['id']), 'courseId');

        $homeworkCourseIds = ArrayToolkit::column($homeworks, 'courseId');
        $homeworkLessonIds = ArrayToolkit::column($homeworks, 'lessonId');
        $homeworkIds = ArrayToolkit::column($homeworks, 'id');

        $courses = $this->getCourseService()->findCoursesByIds($homeworkCourseIds);
        $lessons = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);

        $conditions = array('courseIds' => $homeworkCourseIds, 'role' => 'student');
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchMemberCount($conditions)
            , 25
        );

        $students = $this->getCourseService()->searchMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $homeworkResults = ArrayToolkit::index($this->getHomeworkService()->findResultsByIds(ArrayToolkit::column($homeworks, 'id')), 'homeworkId');

        $reviewingCount = $this->getHomeworkService()->searchResultsCount(array(
            'status' => 'reviewing',
            'checkTeacherId' => $currentUser['id']
        ));
        $finishedCount = $this->getHomeworkService()->searchResultsCount(array(
            'status' => 'finished',
            'checkTeacherId' => $currentUser['id']
        ));

        return $this->render('TopxiaWebBundle:MyHomework:teaching-list.html.twig', array(
            'status' => $status,
            'homeworks' => empty($homeworks) ? array() : $homeworks,
            'users' => $users,
            'homeworkResults' => $homeworkResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'lessons' => $lessons,
            'reviewingCount' => $reviewingCount,
            'finishedCount' => $finishedCount
        ));
    }

    public function listAction(Request $request)
    {   
        $status = $request->query->get('status', 'finished');
        $currentUser = $this->getCurrentUser();

        $conditions = array(
            'status' => $status,
            'userId' => $currentUser['id']
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getHomeworkService()->searchResultsCount($conditions), 
            25
        );
        $homeworkResults = $this->getHomeworkService()->searchResults(
            $conditions, 
            array('usedTime', 'DESC'), 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $homeworkCourseIds = ArrayToolkit::column($homeworkResults, 'courseId');
        $homeworkLessonIds = ArrayToolkit::column($homeworkResults, 'lessonId');
        $courses = $this->getCourseService()->findCoursesByIds($homeworkCourseIds);
        $lessons = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);

        return $this->render('TopxiaWebBundle:MyHomework:list.html.twig',array(
            'status' => $status,
            'homeworkResults' => $homeworkResults,
            'courses' => $courses,
            'lessons' => $lessons,
            'paginator' => $paginator
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Course.HomeworkService');
    }

    protected function getMessageService()
    {
        return $this->getServiceKernel()->createService('User.MessageService');
    }

}