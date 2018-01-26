<?php

namespace Biz\Question\Event;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QuestionAnalysisEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'exam.finish' => 'onTestpaperQuestionAnalysis',
            'exam.reviewed' => 'onTestpaperQuestionAnalysis',
        );
    }

    public function onTestpaperQuestionAnalysis(Event $event)
    {
        $paperResult = $event->getSubject();

        if ('finished' != $paperResult['status'] || !in_array($paperResult['type'], array('testpaper', 'homework'))) {
            return;
        }

        $questions = $this->findFormateQuestions($paperResult['testId']);

        $this->createAnalysisItems($paperResult, $questions);

        $this->updateAnalysisItems($paperResult, $questions);
    }

    protected function findFormateQuestions($paperId)
    {
        $items = $this->getTestpaperService()->findItemsByTestId($paperId);
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));

        foreach ($questions as $key => $question) {
            $questions[$key]['score'] = $items[$question['id']]['score'];
        }

        return $questions;
    }

    protected function createAnalysisItems($paperResult, $questions)
    {
        if (empty($questions)) {
            return false;
        }

        $analysisItems = array();

        $questionIds = ArrayToolkit::column($questions, 'id');
        $analysis = $this->findExistAnalysis($paperResult['testId'], $paperResult['type'], $paperResult['lessonId']);

        foreach ($questions as $question) {
            $choices = $this->getQuestionChoices($question, $paperResult['type']);
            $itemAnalysis = empty($analysis[$question['id']]) ? array() : ArrayToolkit::index($analysis[$question['id']], 'choiceIndex');

            foreach ($choices as $key => $choice) {
                $analysisItem = empty($itemAnalysis[$key]) ? array() : $itemAnalysis[$key];

                if (!$analysisItem) {
                    $fields = array(
                        'targetId' => $paperResult['testId'],
                        'targetType' => $paperResult['type'],
                        'activityId' => $paperResult['lessonId'],
                        'questionId' => $question['id'],
                        'choiceIndex' => $key,
                    );
                    $analysisItems[] = $fields;
                }
            }
        }

        if (!empty($analysisItems)) {
            $this->getQuestionAnalysisService()->batchCreate($analysisItems);
        }

        return true;
    }

    protected function getQuestionChoices($question, $targetType)
    {
        $questionObj = $this->getQuestionService()->getQuestionConfig($question['type']);
        $choices = $questionObj->getAnswerStructure($question);

        if ('homework' == $targetType && 'essay' == $question['type']) {
            return array();
        }

        return $choices;
    }

    protected function updateAnalysisItems($paperResult, $questions)
    {
        //是否之前提交过
        $conditions = array(
            'userId' => $paperResult['userId'],
            'testId' => $paperResult['testId'],
            'type' => $paperResult['type'],
            'lessonId' => $paperResult['lessonId'],
            'status' => 'finished',
        );
        $userResultCount = $this->getTestpaperService()->searchTestpaperResultsCount($conditions);

        $existAnalysis = $this->findExistAnalysis($paperResult['testId'], $paperResult['type'], $paperResult['lessonId']);
        $userAnswers = $this->getTestpaperService()->findItemResultsByResultId($paperResult['id']);

        foreach ($userAnswers as $userAnswer) {
            $question = $questions[$userAnswer['questionId']];
            if (!$userAnswer['answer'] || ('testpaper' != $paperResult['type'] && 'essay' == $question['type'])) {
                continue;
            }

            $questionAnalysis = empty($existAnalysis[$question['id']]) ? array() : $existAnalysis[$question['id']];
            $questionObj = $this->getQuestionService()->getQuestionConfig($question['type']);
            $userAnswerIndexes = $questionObj->analysisAnswerIndex($question, $userAnswer);

            if (empty($userAnswerIndexes)) {
                continue;
            }

            $this->waveNumber($userAnswerIndexes, $questionAnalysis, $userResultCount);
        }
    }

    protected function findExistAnalysis($targetId, $targetType, $activityId)
    {
        $conditions = array(
            'targetId' => $targetId,
            'targetType' => $targetType,
            'activityId' => $activityId,
        );
        $analysis = $this->getQuestionAnalysisService()->searchAnalysis($conditions, array(), 0, PHP_INT_MAX);

        return empty($analysis) ? array() : ArrayToolkit::group($analysis, 'questionId');
    }

    protected function waveNumber($userAnswerIndexes, $existAnalysis, $userResultCount)
    {
        if (empty($userAnswerIndexes)) {
            return;
        }

        $answers = array_shift($userAnswerIndexes);
        $existAnalysis = ArrayToolkit::index($existAnalysis, 'choiceIndex');

        foreach ($answers as $index) {
            if (empty($existAnalysis[$index])) {
                continue;
            }

            $fields = array('totalAnswerCount' => 1);
            if (1 == $userResultCount) {
                $fields['firstAnswerCount'] = 1;
            }
            $this->getQuestionAnalysisService()->waveCount($existAnalysis[$index]['id'], $fields);
        }
    }

    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }

    protected function getQuestionAnalysisService()
    {
        return $this->getBiz()->service('Question:QuestionAnalysisService');
    }
}
