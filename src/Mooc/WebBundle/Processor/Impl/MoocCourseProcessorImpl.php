<?php
namespace Mooc\WebBundle\Processor\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\MobileBundleV2\Processor\Impl\CourseProcessorImpl;

class MoocCourseProcessorImpl extends CourseProcessorImpl
{
    public function getCourse()
    {
        return parent::getCourse();
    }

    public function searchCourse()
    {
        $type = $this->getParam("type", '');

        if (empty($type)) {
            $this->setParam("type", "");
        }

        return parent::searchCourse();
    }

    public function getLearningCourse()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);
        $type  = $this->getParam("type", "");

        $filter = array();

        if (empty($type)) {
            $filter = array("type" => "normal");
        }

        $total   = $this->controller->getCourseService()->findUserLeaningCourseCount($user['id'], $filter);
        $courses = $this->controller->getCourseService()->findUserLeaningCourses($user['id'], $start, $limit, $filter);

        $count = $this->controller->getCourseService()->searchLearnCount(array(
            "userId" => $user["id"]
        ));
        $learnStatusArray = $this->controller->getCourseService()->searchLearns(array(
            "userId" => $user["id"]
        ), array(
            "finishedTime",
            "ASC"
        ), 0, $count);

        $lessons = $this->controller->getCourseService()->findLessonsByIds(ArrayToolkit::column($learnStatusArray, 'lessonId'));

        $tempCourses = array();

        foreach ($courses as $key => $course) {
            $course['periodicStartTime'] = $course['startTime'];
            $course['periodicEndTime']   = $course['endTime'];
            unset($course['startTime']);
            unset($course['endTime']);
            $tempCourses[$course["id"]] = $course;
        }

        $learnStatusArray = $this->coverLearnStatusTime($learnStatusArray);

        foreach ($lessons as $key => $lesson) {
            $courseId = $lesson["courseId"];

            if (isset($tempCourses[$courseId])) {
                $tempCourses[$courseId]["startTime"]       = $learnStatusArray[$courseId];
                $tempCourses[$courseId]["lastLessonTitle"] = $lesson["title"];
            }
        }

        $result = array(
            "start" => $start,
            "limit" => $limit,
            "total" => $total,
            "data"  => $this->controller->filterCourses(array_values($tempCourses))
        );

        return $result;
    }

    private function coverLearnStatusTime($learnStatusArray)
    {
        $map = array();

        foreach ($learnStatusArray as $key => $learnStatus) {
            $map[$learnStatus["courseId"]] = date("c", $learnStatus["startTime"]);
        }

        return $map;
    }
}
