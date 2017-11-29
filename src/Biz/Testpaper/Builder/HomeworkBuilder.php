<?php

namespace Biz\Testpaper\Builder;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\Biz;

class HomeworkBuilder implements TestpaperBuilderInterface
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function build($fields)
    {
        if (!isset($fields['questionIds'])) {
            throw new \InvalidArgumentException('homework field is invalid');
        }
        $questionIds = $fields['questionIds'];

        $fields['status'] = 'open';
        $fields['pattern'] = 'questionType';
        $fields['type'] = 'homework';

        $fields = $this->filterFields($fields);

        $homework = $this->getTestpaperService()->createTestpaper($fields);

        $this->createQuestionItems($homework['id'], $questionIds);

        return $homework;
    }

    public function canBuild($options)
    {
        $questions = $this->getQuestions($options);
        $typedQuestions = ArrayToolkit::group($questions, 'type');

        return $this->canBuildWithQuestions($options, $typedQuestions);
    }

    public function showTestItems($testId, $resultId = 0, $options = array())
    {
        $test = $this->getTestpaperService()->getTestpaperByIdAndType($testId, 'homework');
        $items = $this->getTestpaperService()->findItemsByTestId($test['id']);
        if (!$items) {
            return array();
        }

        $itemResults = array();
        if (!empty($resultId)) {
            $homeworkResult = $this->getTestpaperService()->getTestpaperResult($resultId);

            $itemResults = $this->getTestpaperService()->findItemResultsByResultId($homeworkResult['id'], true);
            $itemResults = ArrayToolkit::index($itemResults, 'questionId');
        }

        $questionIds = ArrayToolkit::column($items, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $formatQuestions = array();
        foreach ($items as $questionId => $item) {
            $question = empty($questions[$questionId]) ? array() : $questions[$questionId];
            if (empty($question)) {
                $question = array(
                    'id' => $item['questionId'],
                    'isDeleted' => true,
                    'stem' => '此题已删除',
                    'score' => 0,
                    'answer' => '',
                    'type' => $item['questionType'],
                );
            }

            if (!empty($itemResults[$questionId])) {
                $question['testResult'] = $itemResults[$questionId];
            }

            $question['score'] = $item['score'];
            $question['seq'] = $item['seq'];
            $question['missScore'] = $item['missScore'];

            if ($item['parentId'] > 0) {
                $formatQuestions[$item['parentId']]['subs'][$questionId] = $question;
            } else {
                $formatQuestions[$item['questionId']] = $question;
            }
        }

        return $formatQuestions;
    }

    public function filterFields($fields, $mode = 'create')
    {
        if (!empty($fields['questionIds'])) {
            $fields['itemCount'] = count($fields['questionIds']);
        }

        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'description',
            'courseId',
            'courseSetId',
            'lessonId',
            'type',
            'status',
            'limitedTime',
            'score',
            'passedCondition',
            'itemCount',
            'copyId',
            'pattern',
            'metas',
        ));

        return $fields;
    }

    public function updateSubmitedResult($resultId, $usedTime)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);
        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($result['testId'], $result['type']);
        $items = $this->getTestpaperService()->findItemsByTestId($result['testId']);
        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($result['id']);

        $questionIds = ArrayToolkit::column($items, 'questionId');

        $hasEssay = $this->getQuestionService()->hasEssay($questionIds);

        $fields = array(
            'status' => $hasEssay ? 'reviewing' : 'finished',
        );

        $accuracy = $this->getTestpaperService()->sumScore($itemResults);
        $fields['objectiveScore'] = $accuracy['sumScore'];
        $fields['rightItemCount'] = $accuracy['rightItemCount'];

        $fields['score'] = 0;

        if (!$hasEssay) {
            $fields['score'] = $fields['objectiveScore'];

            $rightPercent = number_format($accuracy['rightItemCount'] / $homework['itemCount'], 2) * 100;
            $fields['passedStatus'] = $this->getPassedStatus($rightPercent, $homework);
        }

        $fields['usedTime'] = $usedTime + $result['usedTime'];
        $fields['endTime'] = time();

        return $this->getTestpaperService()->updateTestpaperResult($result['id'], $fields);
    }

    protected function getPassedStatus($rightPercent, $homework)
    {
        if (empty($homework['passedCondition']) || count($homework['passedCondition']) <= 1) {
            return 'none';
        }

        if ($rightPercent < $homework['passedCondition'][0]) {
            $passedStatus = 'unpassed';
        } elseif ($rightPercent >= $homework['passedCondition'][0] && $rightPercent < $homework['passedCondition'][1]) {
            $passedStatus = 'passed';
        } elseif ($rightPercent >= $homework['passedCondition'][1] && $rightPercent < $homework['passedCondition'][2]) {
            $passedStatus = 'good';
        } elseif ($rightPercent >= $homework['passedCondition'][2]) {
            $passedStatus = 'excellent';
        } else {
            $passedStatus = 'none';
        }

        return $passedStatus;
    }

    protected function createQuestionItems($homeworkId, $questionIds)
    {
        $homeworkItems = array();
        $index = 0;

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        foreach ($questionIds as $questionId) {
            $question = empty($questions[$questionId]) ? array() : $questions[$questionId];
            if (empty($question)) {
                continue;
            }

            if ('material' != $question['type']) {
                ++$index;
            }

            $items['seq'] = $index;
            $items['questionId'] = $question['id'];
            $items['questionType'] = $question['type'];
            $items['testId'] = $homeworkId;
            $items['parentId'] = $question['parentId'];
            $items['type'] = 'homework';
            $homeworkItems[] = $this->getTestpaperService()->createItem($items);
        }

        $this->getTestpaperService()->updateTestpaper($homeworkId, array('itemCount' => $index));

        return $homeworkItems;
    }

    protected function canBuildWithQuestions($options, $questions)
    {
        $missing = array();

        foreach ($options['counts'] as $type => $needCount) {
            $needCount = intval($needCount);
            if (0 == $needCount) {
                continue;
            }

            if (empty($questions[$type])) {
                $missing[$type] = $needCount;
                continue;
            }
            if ('material' == $type) {
                $validatedMaterialQuestionNum = 0;
                foreach ($questions['material'] as $materialQuestion) {
                    if ($materialQuestion['subCount'] > 0) {
                        $validatedMaterialQuestionNum += 1;
                    }
                }
                if ($validatedMaterialQuestionNum < $needCount) {
                    $missing['material'] = $needCount - $validatedMaterialQuestionNum;
                }
                continue;
            }
            if (count($questions[$type]) < $needCount) {
                $missing[$type] = $needCount - count($questions[$type]);
            }
        }

        if (empty($missing)) {
            return array('status' => 'yes');
        }

        return array('status' => 'no', 'missing' => $missing);
    }

    protected function getQuestions($options)
    {
        $conditions = array();
        $options['ranges'] = array_filter($options['ranges']);

        $conditions['courseSetId'] = $options['courseSetId'];

        if (!empty($options['ranges'])) {
            $conditions['lessonIds'] = $options['ranges'];
        }

        $conditions['parentId'] = 0;

        $total = $this->getQuestionService()->searchCount($conditions);

        return $this->getQuestionService()->search($conditions, array('createdTime' => 'DESC'), 0, $total);
    }

    protected function makeItem($homeworkId, $question)
    {
        return array(
            'testId' => $homeworkId,
            'questionId' => $question['id'],
            'questionType' => $question['type'],
            'parentId' => $question['parentId'],
            'score' => $question['score'],
            'missScore' => 0,
        );
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
