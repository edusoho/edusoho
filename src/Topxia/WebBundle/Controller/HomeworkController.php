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

        $committedCount = $this->getHomeworkService()->searchResultsCount(array(
            'commitStatus' => 'committed',
            'checkTeacherId' => $currentUser['id']
        ));
        $uncommitCount = $this->getCourseService()->searchMemberCount($conditions) - $committedCount;
        $reviewingCount = $this->getHomeworkService()->searchResultsCount(array(
            'status' => 'reviewing',
            'checkTeacherId' => $currentUser['id']
        ));
        $finishedCount = $this->getHomeworkService()->searchResultsCount(array(
            'status' => 'finished',
            'checkTeacherId' => $currentUser['id']
        ));

        if (!empty($homeworkResults)) {
            $students = $this->getHomeworkStudents($status, $students, $homeworkResults);
        } else {
            if ($status != 'uncommitted') {
                $students = array();
            }
        }

        return $this->render('TopxiaWebBundle:MyHomework:teaching-list.html.twig', array(
            'status' => $status,
            'homeworks' => empty($homeworks) ? array() : $homeworks,
            'students' => $students,
            'users' => $users,
            'homeworkResults' => $homeworkResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'lessons' => $lessons,
            'uncommitCount' => $uncommitCount,
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

    private function getHomeworkStudents($status, $students, $homeworkResults)
    {
        if ($status == 'uncommitted') {
            foreach ($students as &$student) {
                foreach ($homeworkResults as $item) {
                    if ($item['status'] != 'doing' && $item['userId'] == $student['userId'] && $item['courseId'] == $student['courseId'] ) {
                        $student = null;
                    }
                }
            }
        } 

        if ($status == 'unchecked') {
            foreach ($students as &$student) {
                $key = false;
                foreach ($homeworkResults as $item) {
                    if ($item['status'] == 'reviewing' && $item['userId'] == $student['userId'] && $item['courseId'] == $student['courseId'] ) {
                        $key = true;
                    }
                }

                if ($key == true) {
                    continue;
                }
                $student = null;
            }
        }

        if ($status == 'finished') {
            foreach ($students as &$student) {
                $key = false;
                foreach ($homeworkResults as $item) {
                    if ($item['status'] == 'finished' && $item['userId'] == $student['userId'] && $item['courseId'] == $student['courseId'] ) {
                        $key = true;
                    }
                }

                if ($key == true) {
                    continue;
                }
                $student = null;
            }
        }
        return array_filter($students);
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