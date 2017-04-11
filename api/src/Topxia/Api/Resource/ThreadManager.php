<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class ThreadManager extends BaseResource
{
    public function question(Application $app, Request $request)
    {
        $courseId = $request->query->get('courseId', 0);
        $start = $request->query->get('start', 5);
        $user = $this->getCurrentUser();

        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            return $this->error('error', '课程信息不存在!');
        }

        if (!$this->getCourseMemberService()->isCourseTeacher($courseId, $user['id'])) {
            return $this->error('error', '您不是老师，不能查看此页面！!');
        }

        $myTeachingCourseCount = $this->getCourseService()->findUserTeachCourseCount(array('userId' => $user['id']), true);

        if (empty($myTeachingCourseCount)) {
            return array(
                'threadCount' => 0,
                'users' => array(),
                'threads' => array(),
            );
        }

        $myTeachingCourses = $this->getCourseService()->findUserTeachCourses(array('userId' => $user['id']), 0, $myTeachingCourseCount, true);

        $conditions = array(
            'courseIds' => array($courseId),
            'type' => 'question',
        );

        $threadCount = $this->getCourseThreadService()->searchThreadCountInCourseIds($conditions);
        $threads = $this->getCourseThreadService()->searchThreadInCourseIds(
            $conditions,
            'posted',
            0,
            500
        );

        $threads = $this->sortThreads($threads, $start);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        $lessons = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($threads, 'taskId'));
        $lessons = ArrayToolkit::index($lessons, 'id');

        foreach ($threads as $key => &$thread) {
            $lesson = empty($lessons[$thread['taskId']]) ? array() : $lessons[$thread['taskId']];
            $lessonTitle = empty($lesson) ? '课程提问' : '课时:'.$lesson['number'].$lesson['title'];
            $thread['lessonTitle'] = $lessonTitle;
            $thread['isTeacherAnswer'] = $this->getCourseThreadService()->getPostCountByuserIdAndThreadId($user['id'], $thread['id']);
        }

        return array(
            'threadCount' => $threadCount,
            'users' => $this->simpleUsers($users),
            'threads' => $this->muiltFilter($threads),
        );
    }

    protected function threadSort($t1, $t2)
    {
        $latestPostTime1 = $t1['latestPostTime'];
        $latestPostTime2 = $t2['latestPostTime'];

        if ($latestPostTime1 > 0) {
            if ($latestPostTime2 > 0) {
                return $latestPostTime1 - $latestPostTime2;
            }

            return -1;
        }

        if ($latestPostTime2 > 0) {
            return -1;
        }

        return $latestPostTime1 - $latestPostTime2;
    }

    private function sortThreads($threads, $limit)
    {
        usort($threads, array($this, 'threadSort'));
        $threads = array_slice($threads, 0, $limit);

        return $threads;
    }

    protected function muiltFilter($res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->filter($one);
        }

        return $res;
    }

    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['latestPostTime'] = date('c', $res['latestPostTime']);

        return $res;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getCourseThreadService()
    {
        return $this->getServiceKernel()->createService('Course:ThreadService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }
}
