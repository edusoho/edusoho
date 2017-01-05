<?php
namespace Biz\Testpaper\Builder;

use Topxia\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\Biz;
use Biz\Testpaper\Builder\TestpaperBuilderInterface;

class ExerciseBuilder implements TestpaperBuilderInterface
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

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
                'courseId' => $exercise['courseSetId'],
                'parentId' => 0
            );
            if (!empty($exercise['metas']['difficulty'])) {
                $conditions['difficulty'] = $exercise['metas']['difficulty'];
            }

            if (!empty($exercise['metas']['range']) && $exercise['metas']['range'] == 'lesson') {
                $conditions['lessonId'] = $exercise['lessonId'];
            }

            $count     = $this->getQuestionService()->searchCount($conditions);
            $questions = $this->getQuestionService()->search(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                $count
            );
            shuffle($questions);

            $questions = array_slice($questions, 0, $exercise['itemCount']);
        }

        return $this->formatQuestions($questions, $itemResults);
    }

    public function filterFields($fields, $mode = 'create')
    {
        if (!empty($fields['questionTypes'])) {
            $fields['metas']['questionTypes'] = $fields['questionTypes'];
        }

        if (!empty($fields['difficulty'])) {
            $fields['metas']['difficulty'] = $fields['difficulty'];
        }

        if (!empty($fields['range'])) {
            $fields['metas']['range'] = $fields['range'];
        }

        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'itemCount',
            'courseId',
            'courseSetId',
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
        $index           = 1;

        foreach ($questions as $question) {
            if (!empty($questionResults[$question['id']])) {
                $question['testResult'] = $questionResults[$question['id']];
            }

            $question['seq'] = $index;

            if ($question['subCount'] > 0) {
                $subQuestions = $this->getQuestionService()->findQuestionsByParentId($question['id']);

                $question['subs'] = $subQuestions;
            }

            $formatQuestions[$question['id']] = $question;

            $index++;
        }

        return $formatQuestions;
    }

    protected function getQuestions($options)
    {
        $conditions = array();

        if (!empty($options['range']) && $options['range'] != 'course') {
            $conditions['lessonIds'] = $options['range'];
        }

        if (!empty($options['questionTypes'])) {
            $conditions['types'] = $options['questionTypes'];
        }

        if (!empty($options['difficulty'])) {
            $conditions['difficulty'] = $options['difficulty'];
        }
        $conditions['courseId'] = $options['courseId'];
        $conditions['parentId'] = 0;

        $total = $this->getQuestionService()->searchCount($conditions);

        return $this->getQuestionService()->search($conditions, array('createdTime' => 'DESC'), 0, $total);
    }

    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }
}
