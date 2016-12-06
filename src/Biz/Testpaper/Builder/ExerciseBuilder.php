<?php
namespace Biz\Testpaper\Builder;

use Biz\Factory;
use Topxia\Common\ArrayToolkit;
use Biz\Testpaper\Builder\TestpaperLibBuilder;
use Topxia\Common\Exception\InvalidArgumentException;

class ExerciseBuilder extends Factory implements TestpaperLibBuilder
{
    public function build($fields)
    {
        $fields = $this->filterFields($fields);
        return $this->getTestpaperService()->createTestpaper($fields);
    }

    public function canBuild($options)
    {
        $questions     = $this->getQuestions($options);
        $questionCount = count($questions);

        if ($questionCount < $options['itemCount']) {
            $lessNum = $options['itemCount'] - $questionCount;
            return array('status' => 'no', 'lessNum' => $lessNum);
        } else {
            return array('status' => 'yes');
        }
    }

    public function showTestItems($testId, $resultId)
    {
        $exercise = $this->getTestpaperService()->getTestpaper($testId);

        $itemResults = array();
        if ($resultId) {
            $exerciseResult = $this->getTestpaperService()->getTestpaperResult($resultId);

            $itemResults = $this->getTestpaperService()->findItemResultsByResultId($exerciseResult['id']);
            $itemResults = ArrayToolkit::index($itemResults, 'questionId');
        }

        if ($itemResults) {
            $questionIds = ArrayToolkit::column($itemResults, 'questionId');
            $questions   = $this->getQuestionService()->findQuestionsByIds($questionIds);
        } else {
            $conditions = array(
                'types'    => $exercise['metas']['questionTypes'],
                'courseId' => $exercise['courseId'],
                'parentId' => 0
            );
            if (!empty($exercise['metas']['difficulty'])) {
                $conditions['difficulty'] = $exercise['metas']['difficulty'];
            }

            if (!empty($exercise['metas']['range']) && $exercise['metas']['range'] == 'lesson') {
                $conditions['lessonId'] = $exercise['lessonId'];
            }

            $questions = $this->getQuestionService()->search(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                $exercise['itemCount']
            );
        }

        return $this->formatQuestions($questions, $itemResults);
    }

    public function filterFields($fields, $mode = 'create')
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId'))) {
            throw new \InvalidArgumentException('exercise field is invalid');
        }

        $filtedFields = array();

        $filtedFields['itemCount'] = $fields['itemCount'];
        $filtedFields['courseId']  = $fields['courseId'];
        $filtedFields['lessonId']  = $fields['lessonId'];
        $filtedFields['type']      = 'exercise';
        $filtedFields['status']    = 'open';
        $filtedFields['pattern']   = 'questionType';
        $filtedFields['copyId']    = empty($fields['copyId']) ? 0 : $fields['copyId'];
        $filtedFields['metas']     = empty($fields['metas']) ? array() : $fields['metas'];
        $filtedFields['name']      = empty($fields['name']) ? '' : $fields['name'];

        $filtedFields['metas']['questionTypes'] = empty($fields['questionTypes']) ? array() : $fields['questionTypes'];
        $filtedFields['metas']['difficulty']    = empty($fields['difficulty']) ? '' : $fields['difficulty'];
        $filtedFields['metas']['range']         = empty($fields['range']) ? 'course' : $fields['range'];

        $filtedFields['passedCondition'] = array(0);

        return $filtedFields;
    }

    public function updateSubmitedResult($resultId, $usedTime)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);
        $itemResults     = $this->getTestpaperService()->findItemResultsByResultId($testpaperResult['id']);

        $fields = array(
            'status' => 'finished'
        );

        $accuracy                 = $this->getTestpaperService()->sumScore($itemResults);
        $fields['score']          = $accuracy['sumScore'];
        $fields['rightItemCount'] = $accuracy['rightItemCount'];

        $fields['usedTime']    = $usedTime + $testpaperResult['usedTime'];
        $fields['endTime']     = time();
        $fields['checkedTime'] = time();

        return $this->getTestpaperService()->updateTestpaperResult($testpaperResult['id'], $fields);
    }

    protected function formatQuestions($questions, $questionResults)
    {
        $formatQuestions = array();
        $i               = 1;
        foreach ($questions as $question) {
            if (!empty($questionResults[$question['id']])) {
                $question['testResult'] = $questionResults[$question['id']];
            }

            $question['seq'] = $i;

            if ($question['parentId'] > 0) {
                $formatQuestions[$question['parentId']]['subs'][$question['id']] = $question;
            } else {
                $formatQuestions[$question['id']] = $question;
            }
            $i++;
        }

        return $formatQuestions;
    }

    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }
}
