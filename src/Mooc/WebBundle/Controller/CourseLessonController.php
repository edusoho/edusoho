<?php

namespace Mooc\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\CourseLessonController as BaseCourseLessonController;

class CourseLessonController extends BaseCourseLessonController
{
    public function doTestpaperAction(Request $request, $lessonId, $testId)
    {
        $status  = 'do';
        $message = $this->checkTestPaper($lessonId, $testId, $status);

        if (!empty($message)) {
            return $this->createMessageResponse('info', $message);
        }

        return $this->forward('TopxiaWebBundle:Testpaper:doTestpaper', array('targetType' => 'lesson', 'targetId' => $lessonId, 'testId' => $testId));
    }

    public function reDoTestpaperAction(Request $request, $lessonId, $testId)
    {
        $status  = 'redo';
        $message = $this->checkTestPaper($lessonId, $testId, $status);

        if (!empty($message)) {
            return $this->createMessageResponse('info', $message);
        }

        return $this->forward('TopxiaWebBundle:Testpaper:reDoTestpaper', array('targetType' => 'lesson', 'targetId' => $lessonId, 'testId' => $testId));
    }

    private function checkTestPaper($lessonId, $testId, $status)
    {
        $user = $this->getCurrentUser();

        $message   = '';
        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        $targets = $this->get('topxia.target_helper')->getTargets(array($testpaper['target']));

        if ($targets[$testpaper['target']]['type'] != 'course') {
            throw $this->createAccessDeniedException('试卷只能属于课程');
        }

        $courseId = $targets[$testpaper['target']]['id'];

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            return $message = '试卷所属课程不存在！';
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $message = '不是试卷所属课程老师或学生';
        }

        if ($course['type'] == 'periodic' && time() > $course['endTime']) {
            return $message = '周期课程已经结束, 不可考试';
        }

        $lesson = $this->getCourseService()->getLesson($lessonId);

        if ($lesson['testMode'] == 'realTime') {
            $testpaper = $this->getTestpaperService()->getTestpaper($testId);

            $testEndTime = $lesson['testStartTime'] + $testpaper['limitedTime'] * 60;

            if ($testEndTime < time()) {
                return $message = '实时考试已经结束!';
            }

            if ($status == 'do') {
                $testpaperResult = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testpaper['id'], $user['id'], array('finished'));

                if ($testpaperResult) {
                    return $message = '您已经提交试卷，不能继续考试!';
                }
            } else {
                return $message = '实时考试，不能再考一次!';
            }
        }
    }
}
