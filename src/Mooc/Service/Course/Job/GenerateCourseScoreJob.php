<?php
namespace Mooc\Service\Course\Job;

use Topxia\Common\PluginToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Crontab\Job;

class GenerateCourseScoreJob implements Job
{
    public function execute($params)
    {
        if (empty($params['courseId'])) {
            throw \InvalidArgumentException("courseId参数不正确！");
        }

        $courseId = $params['courseId'];
        $course   = $this->getCourseService()->tryManageCourse($courseId);

        if (empty($course)) {
            throw \InvalidArgumentException("课程{$courseId}不存在！");
        }

        $scoreSetting = $this->getCourseScoreService()->getScoreSettingByCourseId($courseId);

        if (empty($scoreSetting)) {
            return;
        }

        $homeworks = array();

        if (PluginToolkit::isPluginInstalled('Homework')) {
            $homeworks = $this->getHomeworkService()->findHomeworksByCourseId($courseId);
        }

        $testpaperPercentage  = $scoreSetting['examWeight'] / 100;
        $homeworkPercentage   = $scoreSetting['homeworkWeight'] / 100;
        $students             = $this->getCourseService()->findCourseStudentsAll($courseId);
        $conditions           = array();
        $conditions['target'] = "course-{$course['id']}";
        $testpapers           = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime', 'ASC'),
            0,
            1000
        );

        foreach ($students as $student) {
            $testpaperScore = $this->getStudentTestpaperScore($student, $testpapers, $testpaperPercentage);
            $homeworkScore  = $this->getStudentHomeworkScore($student, $homeworks, $homeworkPercentage);
            $userScore      = $this->getCourseScoreService()->getUserScoreByUserIdAndCourseId($student['userId'], $courseId);

            if (empty($userScore)) {
                $userScore = array(
                    'courseId'      => $courseId,
                    'userId'        => $student['userId'],
                    'totalScore'    => $testpaperScore + $homeworkScore,
                    'examScore'     => $testpaperScore,
                    'homeworkScore' => $homeworkScore
                );
                $this->getCourseScoreService()->addUserCourseScore($userScore);
            } else {
                $userScore['examScore']     = $testpaperScore;
                $userScore['homeworkScore'] = $homeworkScore;
                $userScore['totalScore']    = $homeworkScore + $testpaperScore + $userScore['otherScore'];
                $this->getCourseScoreService()->updateUserCourseScore($userScore['id'], $userScore);
            }
        }

        if ('scoring' == $scoreSetting['status']) {
            $this->getCourseScoreService()->updateScoreSetting($courseId, array('status' => 'unpublish'));
        }
    }

    protected function getStudentHomeworkScore($student, $homeworks, $homeworkPercentage)
    {
        $totalScore = 0;
        $count      = 0;
        $average    = 0;

        foreach ($homeworks as $homework) {
            $homeworkScore = 0;
            $result        = $this->getHomeworkService()->getResultByHomeworkIdAndUserId($homework['id'], $student['userId']);

            if (!empty($result)) {
                $homeworkScore = $result['score'];
                $homeworkScore = $homeworkScore / $homework['score'] * 100;
            }

            $count++;
            $totalScore = $totalScore + $homeworkScore;
        }

        if ($totalScore > 0 && $count > 0) {
            $average = $totalScore / $count;
        }

        $finalScore = $average * $homeworkPercentage;
        return $finalScore;
    }

    protected function getStudentTestpaperScore($student, $testpapers, $testpaperPercentage)
    {
        $totalScore = 0;
        $count      = 0;
        $average    = 0;

        foreach ($testpapers as $testpaper) {
            $testpaperScore = 0;
            $result         = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testpaper['id'], $student['userId'], array('finished'));

            if (!empty($result)) {
                $testpaperScore = $result['score'];
                $testpaperScore = $testpaperScore / $testpaper['score'] * 100;
            }

            $count++;
            $totalScore = $totalScore + $testpaperScore;
        }

        if ($totalScore > 0 && $count > 0) {
            $average = $totalScore / $count;
        }

        $finalScore = $average * $testpaperPercentage;
        return $finalScore;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getCourseScoreService()
    {
        return $this->getServiceKernel()->createService('Mooc:Course.CourseScoreService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
