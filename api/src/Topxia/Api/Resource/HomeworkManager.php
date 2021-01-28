<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class HomeworkManager extends BaseResource
{
    public function check(Application $app, Request $request, $homeworkResultId)
    {
        $homeworkResource = $app['res.Homework'];
        $homework = $homeworkResource->result($app, $request, $homeworkResultId);

        if (empty($homework) || isset($homework['error'])) {
            return $this->error($homework['error']['code'], $homework['error']['message']);
        }

        $currentUser = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $checkHomeworkData = $request->request->all();
            $checkHomeworkData = empty($checkHomeworkData['data']) ? '' : $checkHomeworkData['data'];
            $this->getHomeworkService()->checkHomework($homeworkResultId, $userId, $checkHomeworkData);

            return $this->createJsonResponse(
                array(
                    'courseId' => $courseId,
                    'lessonId' => $homework['lessonId'],
                )
            );
        }

        return $homework;
    }

    public function teaching(Application $app, Request $request)
    {
        $start = $request->query->get('start', 5);
        $courseId = $request->query->get('courseId', 0);
        $status = $request->query->get('status', 'reviewing');
        $currentUser = $this->getCurrentUser();
        if (empty($currentUser)) {
            return $this->error('500', '用户不存在或者尚未登录，请先登录');
        }

        if (!$this->getCourseMemberService()->isCourseTeacher($courseId, $currentUser['id'])) {
            return $this->error('error', '您不是老师，不能查看此页面！');
        }

        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            return $this->error('error', '课程信息不存在!');
        }

        $courseIds = array($courseId);
        $homeworksResultsCounts = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, $status);

        if ($status == 'reviewing') {
            $orderBy = array('usedTime'=> 'DESC');
        }

        if ($status == 'finished') {
            $orderBy = array('checkedTime'=> 'DESC');
        }

        $homeworksResults = $this->getHomeworkService()->findResultsByCourseIdsAndStatus($courseIds, $status, $orderBy, 0, $start);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($homeworksResults, 'courseId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($homeworksResults, 'lessonId'));

        $usersIds = ArrayToolkit::column($homeworksResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($usersIds);

        foreach ($homeworksResults as $key => &$value) {
            $value['lessonTitle'] = $lessons[$value['lessonId']]['title'];
        }

        return array(
            'homeworksResultsCounts' => $homeworksResultsCounts,
            'users' => $this->simpleUsers($users),
            'homeworkResults' => $this->muiltFilter($homeworksResults),
        );
    }

    protected function muiltFilter($res)
    {
        foreach ($res as &$one) {
            $this->filter($one);
        }

        return $res;
    }

    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        $res['usedTime'] = date('c', $res['usedTime']);

        return $res;
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
