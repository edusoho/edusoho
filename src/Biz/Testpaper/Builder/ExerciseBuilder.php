<?php
namespace Biz\Testpaper\Builder;

use Biz\Factory;
use Topxia\Common\ArrayToolkit;
use Biz\Testpaper\Builder\TestpaperLibBuilder;

class ExerciseBuilder extends Factory implements TestpaperLibBuilder
{
    public function build($fields)
    {
        $fields['type']            = 'exercise';
        $fields['status']          = 'open';
        $fields['pattern']         = 'questionType';
        $fields['passedCondition'] = array(0);

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

    public function showTestItems($testId, $resultId = 0)
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
        if (!empty($fields['questionTypes'])) {
            $filtedFields['metas']['questionTypes'] = $fields['questionTypes'];
        }

        if (!empty($fields['questionTypes'])) {
            $filtedFields['metas']['difficulty'] = $fields['difficulty'];
        }

        if (!empty($fields['range'])) {
            $filtedFields['metas']['range'] = $fields['range'];
        }

        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'itemCount',
            'courseId',
            'lessonId',
            'type',
            'status',
            'pattern',
            'copyId',
            'metas',
            'passedCondition'
        ));

        return $fields;
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
