<?php
namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class LessonWatchTime extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $lessonId = $request->request->get('lessonId');
        $watchTime = $request->request->get('watchTime');

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->error('not_login', '尚未登录');
        }

        $lesson = $this->getCourseService()->getLesson($lessonId);
        if (empty($lesson)) {
            return $this->error('not_lessonId', '课时不存在');
        }

        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        if (empty($course)) {
            return $this->error('not_courseId', '课程不存在');
        }

        $lessonLearn = $this->getCourseService()->getLearnByUserIdAndLessonId($user['id'], $lessonId);
        if (empty($lessonLearn)) {
            return $this->error('not_lesson_learn', '课时学习数据不存在');
        }

        $learn = $this->getCourseService()->waveWatchingTime($user['id'], $lessonId, $watchTime);
        return $learn;
    }

    public function filter($res)
    {
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}