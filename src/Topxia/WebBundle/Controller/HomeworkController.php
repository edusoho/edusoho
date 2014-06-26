<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class HomeworkController extends BaseController
{

    public function urgeAction (Request $request, $homeworkId, $userId)
    {
        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createServiceException('作业不存在或者已被删除！');
        }

        $course = $this->getCourseService()->getCourse($homework['courseId']);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $homework['lessonId']);
        $student = $this->getUserService()->getUser($userId);
        $teacher = $this->getCurrentUser();

        if (empty($teacher)) {
            throw $this->createServiceException('用户不存在或者尚未登录，请先登录');
        }

        if (empty($student)) {
            throw $this->createServiceException('学生不存在或者已删除，请查验后再发送！');
        }

        if (empty($course)) {
            throw $this->createServiceException('课程不存在或已被删除！');
        }

        if (empty($lesson)) {
            throw $this->createServiceException('课时不存在或者已被删除！');
        }

        $message = $this->getUrgeMessageBody($course, $lesson);
        $this->getMessageService()->sendMessage($teacher['id'], $student['id'], $message);

        return $this->createJsonResponse(true);
    }

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
        $homeworkResults = ArrayToolkit::index($this->getHomeworkService()->findHomeworkResultsByHomeworkIds(ArrayToolkit::column($homeworks, 'id')), 'homeworkId');

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
            'lessons' => $lessons
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
            $this->getHomeworkService()->searchHomeworkResultsCount($conditions), 
            25
        );
        $homeworkResults = $this->getHomeworkService()->searchHomeworkResults(
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

        if ($status == 'checked') {
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

    private function getUrgeMessageBody($course, $lesson)
    {   
        $urgeMessageBody = '你的作业还没提交<a href="'. $this->generateUrl('course_learn', array('id' =>$course['id'])) .'#lesson/'.$lesson['id'].'">（'.$course['title'].'第'.$lesson['number'].'课）</a>，请及时完成并提交。';

        return $urgeMessageBody;
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