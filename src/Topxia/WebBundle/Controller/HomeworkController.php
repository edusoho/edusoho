<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

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

    public function listAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $homeworkResults = $this->getHomeworkService()->searchHomeworkResults(array('userId' => $currentUser['id']), array('createdTime','DESC'), 0, 100);
        var_dump($homeworkResults);exit();
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