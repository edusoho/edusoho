<?php

namespace Topxia\Api\Resource\AnalysisType;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class Course extends BaseAnalysisType
{

    public function loginStudentByDay($courseId)
    {
        
    }

    private function learnLessonByDay($courseId)
    {
        $timeRange = array('startTime' => strtotime(date("Y-m-d", time() - 7 * 24 * 3600)), 'endTime' => strtotime(date("Y-m-d 23:59:59", time())));;
        $condition = array(
            "startTime" => $timeRange['startTime'], 
            "endTime" => $timeRange['endTime'], 
            "status" => "finished",
            "courseId" => $courseId
        );

        $count = $this->getCourseService()->searchLearnCount(
            $condition
        );
        $finishedLessonDetail = $this->getCourseService()->searchLearns(
            $condition,
            array("finishedTime", "DESC"),
            0,
            1000
        );

        $userIds = ArrayToolkit::column($finishedLessonDetail, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $finishedLessonStartData = $this->getCourseService()->searchLearns(array("status" => "finished"), array("finishedTime", "ASC"), 0, 1);

        if ($finishedLessonStartData) {
            $finishedLessonStartDate = date("Y-m-d", $finishedLessonStartData[0]['finishedTime']);
        }

        $chartData = array();
        $pointData = array();
        for ($i=6; $i >= 0; $i--) { 
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
        $studentNum = (float)$course["studentNum"];
        $totalPercent = $pointData[date("m-d", time())];
        $totalPercent = (int)($totalPercent / $studentNum * 100);

        $header = array(
            array(
                "title"=>"每日一课完成率",
                "value"=>($totalPercent > 100 ? 100 : $totalPercent) . "%"
            ),
            array(
                "title"=>"学员数",
                "value"=>$studentNum
            )
        );

        return array(
            "header" => $header,
            "chartLineColor"=>"#37b97d",
            "chartLabel"=>"每日完成一课时（及以上）人数",
            "labelData"=>array_keys($chartData),
            "pointData"=>array_values($pointData)
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

        if (!$this->getCourseService()->isCourseTeacher($courseId, $user["id"])) {
            return $this->error('error', '没有权限查看!'); 
        }

        $learnLessonByDayArray = $this->learnLessonByDay($courseId);
        return array(
            $learnLessonByDayArray
        );
	}

	private function getCourseService()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}

	private function getUserService()
	{
		return $this->getServiceKernel()->createService('User.UserService');
	}
}