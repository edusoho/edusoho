<?php

namespace Topxia\Api\Resource\AnalysisType;

use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class Course extends BaseAnalysisType
{
    public function loginStudentByDay($courseId)
    {
    }

    private function learnLessonByDay($courseId)
    {
        $timeRange = array('startTime' => strtotime(date("Y-m-d", time() - 7 * 24 * 3600)), 'endTime' => strtotime(date("Y-m-d 23:59:59", time())));
        $condition = array(
            'finishedTime_GE' => $timeRange['startTime'],
            'finishedTime_LE' => $timeRange['endTime'],
            'status' => 'finish',
            'courseId' => $courseId,
        );

        $count = $this->getTaskResultService()->countTaskResults(
            $condition
        );

        $finishedLessonDetail = $this->getTaskResultService()->searchTaskResults(
            $condition,
            array('finishedTime' => 'desc'),
            0,
            $count
        );

        $userIds = ArrayToolkit::column($finishedLessonDetail, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $chartData = array();
        $pointData = array();
        for ($i = 6; $i >= 0; $i--) {
            $chartData[date("m-d", time() - $i * 24 * 3600)] = array();
            $pointData[date("m-d", time() - $i * 24 * 3600)] = 0;
        }

        foreach ($finishedLessonDetail as $key => $detail) {
            $userId = $detail["userId"];
            $finishedTime = $detail["finishedTime"];
            $finishedTime = date("m-d", $finishedTime);

            if (!isset($chartData[$finishedTime])) {
                continue;
            }

            if (!isset($chartData[$finishedTime][$userId])) {
                $pointData[$finishedTime] += 1;
                $chartData[$finishedTime][$userId] = $users[$userId]["nickname"];
            }
        }

        $course = $this->getCourseService()->getCourse($courseId);
        $studentNum = (float) $course["studentNum"];
        $totalPercent = $pointData[date("m-d", time())];
        $totalPercent = (int) ($studentNum ? ($totalPercent / $studentNum * 100) : 0);

        $header = array(
            array(
                "title" => "每日一课完成率",
                "value" => ($totalPercent > 100 ? 100 : $totalPercent)."%",
            ),
            array(
                "title" => "学员数",
                "value" => $studentNum,
            ),
        );

        return array(
            "header" => $header,
            "chartLineColor" => "#37b97d",
            "chartLabel" => "每日完成一课时（及以上）人数",
            "labelData" => array_keys($chartData),
            "pointData" => array_values($pointData),
        );
    }

    public function learnDataByDay()
    {
        $courseId = $this->request->get("courseId", 0);
        if (empty($courseId)) {
            return $this->error('error', 'courseId not empty!');
        }

        $user = $this->getCurrentUser();
        if (empty($user)) {
            return $this->error('error', '请登录后查看!');
        }

        if (!$this->getCourseMemberService()->isCourseTeacher($courseId, $user["id"])) {
            return $this->error('error', '没有权限查看!');
        }

        $learnLessonByDayArray = $this->learnLessonByDay($courseId);
        return array(
            $learnLessonByDayArray,
        );
    }

    private function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    private function getTaskResultService()
    {
        return ServiceKernel::instance()->createService('Task:TaskResultService');
    }

    protected function getCourseMemberService()
    {
        return ServiceKernel::instance()->createService('Course:MemberService');
    }
}
