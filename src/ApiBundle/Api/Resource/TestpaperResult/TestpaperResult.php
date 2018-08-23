<?php

namespace ApiBundle\Api\Resource\TestpaperResult;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Question\Service\QuestionService;
use Biz\System\Service\SettingService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TestpaperResult extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        $data = $request->request->all();
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($data['resultId']);

        if (!empty($testpaperResult) && !in_array($testpaperResult['status'], array('doing', 'paused'))) {
            throw new AccessDeniedHttpException('试卷已经做完，不能重复提交!');
        }

        $testpaperResult = $this->getTestpaperService()->finishTest($testpaperResult['id'], $data);
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        if ($testpaperResult['userId'] != $user['id']) {
            $course = $this->getCourseService()->tryManageCourse($testpaperResult['courseId']);
        }

        if (empty($course) && $testpaperResult['userId'] != $user['id']) {
            throw new AccessDeniedHttpException('不可以访问其他学生的试卷哦!');
        }

        $items = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return array(
            'testpaper' => $testpaper,
            'testpaperResult' => $testpaperResult,
        );
    }

    public function get(ApiRequest $request, $resultId)
    {
        $questionSetting = $this->getSettingService()->get('questions', array());

        $answerShowMode = empty($questionSetting['testpaper_answers_show_mode']) ? 'submitted' : $questionSetting['testpaper_answers_show_mode'];

        $resultShow = true;
        // 不显示题目
        if ('hide' == $answerShowMode) {
            $resultShow = false;
        }

        $user = $this->getCurrentUser();
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);
        if (!$testpaperResult || $testpaperResult['userId'] != $user['id']) {
            throw new AccessDeniedHttpException('不可以访问其他学生的试卷哦!');
        }

        //客观题自动批阅完后先显示答案解析
        if ('reviewed' == $answerShowMode && 'finished' != $testpaperResult['status']) {
            $resultShow = false;
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        if ($testpaperResult['userId'] != $user['id']) {
            $course = $this->getCourseService()->tryManageCourse($testpaperResult['courseId']);
        }

        if (empty($course) && $testpaperResult['userId'] != $user['id']) {
            throw new AccessDeniedHttpException('不可以访问其他学生的试卷哦!');
        }

        $accuracy = $this->getTestpaperService()->makeAccuracy($testpaperResult['id']);

        $favorites = $this->getQuestionService()->findUserFavoriteQuestions($user['id']);

        if ($resultShow) {
            $items = $this->getTestpaperService()->showTestpaperItems($testpaper['id'], $testpaperResult['id']);
        } else {
            $items = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);
        }
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return array(
            'testpaper' => $testpaper,
            'items' => $items,
            'accuracy' => $accuracy,
            'testpaperResult' => $testpaperResult,
            'favorites' => ArrayToolkit::column($favorites, 'questionId'),
            'resultShow' => $resultShow,
        );
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->service('Testpaper:TestpaperService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->service('Question:QuestionService');
    }
}
