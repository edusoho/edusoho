<?php
namespace Biz\Testpaper\Builder;

use Biz\Factory;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\Exception\RuntimeException;
use Biz\Testpaper\Builder\TestpaperLibBuilder;
use Biz\Testpaper\Pattern\TestpaperPatternFactory;
use Topxia\Service\Question\Type\QuestionTypeFactory;

class TestpaperBuilder extends Factory implements TestpaperLibBuilder
{
    public function build($fields)
    {
        $fields = $this->filterFields($fields);

        $testpaper = $this->getTestpaperService()->createTestpaper($fields);

        $testpaperPattern = TestpaperPatternFactory::create($this->getBiz(), $testpaper['pattern']);

        $testpaper['metas']['courseId'] = $testpaper['courseId'];

        $result = $testpaperPattern->getTestpaperQuestions($testpaper, $testpaper['metas']);

        if ($result['status'] != 'ok') {
            throw new \RuntimeException("Build testpaper #{$result['id']} items error.");
        }

        $this->createQuestionItems($result['items']);

        return $testpaper;
    }

    public function canBuild($options)
    {
        $questions      = $this->getQuestions($options);
        $typedQuestions = ArrayToolkit::group($questions, 'type');
        return $this->canBuildWithQuestions($options, $typedQuestions);
    }

    public function showTestItems($testId, $resultId = 0)
    {
        $test  = $this->getTestpaperService()->getTestpaper($testId);
        $items = $this->getTestpaperService()->findItemsByTestId($test['id']);
        if (!$items) {
            return array();
        }

        $itemResults = array();
        if (!empty($resultId)) {
            $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

            $itemResults = $this->getTestpaperService()->findItemResultsByResultId($testpaperResult['id']);
            $itemResults = ArrayToolkit::index($itemResults, 'questionId');
        }

        $questionIds = ArrayToolkit::column($items, 'questionId');
        $questions   = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $formatItems = array();
        foreach ($items as $questionId => $item) {
            $question = empty($questions[$questionId]) ? array() : $questions[$questionId];
            if (!$question) {
                $question = array(
                    'isDeleted' => true,
                    'stem'      => $this->getServiceKernel()->trans('此题已删除'),
                    'score'     => 0,
                    'answer'    => ''
                );
            }

            $question['score']     = $item['score'];
            $question['seq']       = $item['seq'];
            $question['missScore'] = $item['missScore'];

            if (!empty($itemResults[$questionId])) {
                $question['testResult'] = $itemResults[$questionId];
            }

            if ($question['parentId'] > 0) {
                $formatItems['material'][$item['parentId']]['subs'][$questionId] = $question;
            } else {
                $formatItems[$item['questionType']][$questionId] = $question;
            }
        }

        return $formatItems;
    }

    public function filterFields($fields, $mode = 'create')
    {
        if (isset($fields['mode'])) {
            $fields['metas']['mode'] = $fields['mode'];
        }
        if (isset($fields['range'])) {
            $fields['metas']['range'] = $fields['range'];
        }
        if (isset($fields['ranges'])) {
            $fields['metas']['ranges'] = $fields['ranges'];
        }
        if (isset($fields['counts'])) {
            $fields['metas']['counts'] = $fields['counts'];
        }
        if (isset($fields['scores'])) {
            $fields['metas']['scores'] = $fields['scores'];
        }
        if (isset($fields['missScores'])) {
            $fields['metas']['missScores'] = $fields['missScores'];
        }
        if (isset($fields['scores'])) {
            $fields['metas']['percentages'] = $fields['percentages'];
        }

        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'description',
            'courseId',
            'lessonId',
            'type',
            'status',
            'limitedTime',
            'score',
            'passedCondition',
            'itemCount',
            'copyId',
            'pattern',
            'metas'
        ));

        return $fields;
    }

    public function updateSubmitedResult($resultId, $usedTime)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);
        $testpaper       = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
        $items           = $this->getTestpaperService()->findItemsByTestId($testpaperResult['testId']);
        $itemResults     = $this->getTestpaperService()->findItemResultsByResultId($testpaperResult['id']);

        $questionIds = ArrayToolkit::column($items, 'questionId');

        $hasEssay = $this->getQuestionService()->hasEssay($questionIds);

        $fields = array(
            'status' => $hasEssay ? 'reviewing' : 'finished'
        );

        $accuracy                 = $this->getTestpaperService()->sumScore($itemResults);
        $fields['objectiveScore'] = $accuracy['sumScore'];

        $fields['score'] = 0;

        if (!$hasEssay) {
            $fields['score']       = $fields['objectiveScore'];
            $fields['checkedTime'] = time();
        }

        $fields['passedStatus'] = $fields['score'] >= $testpaper['passedCondition'][0] ? 'passed' : 'unpassed';

        $fields['usedTime'] = $usedTime + $testpaperResult['usedTime'];
        $fields['endTime']  = time();

        $fields['rightItemCount'] = $accuracy['rightItemCount'];

        return $this->getTestpaperService()->updateTestpaperResult($testpaperResult['id'], $fields);
    }

    protected function createQuestionItems($questions)
    {
        $testpaperItems = array();
        $seq            = 1;

        foreach ($questions as $item) {
            $questionType = QuestionTypeFactory::create($item['questionType']);

            $item['seq'] = $seq;

            if (!$questionType->canHaveSubQuestion()) {
                $seq++;
            }

            $testpaperItems[] = $this->getTestpaperService()->createItem($item);
        }

        return $testpaperItems;
    }

    protected function getQuestions($options)
    {
        $conditions        = array();
        $options['ranges'] = array_filter($options['ranges']);

        if (!empty($options['ranges'])) {
            $conditions['lessonIds'] = $options['ranges'];
        }
        $conditions['courseId'] = $options['courseId'];
        $conditions['parentId'] = 0;

        $total = $this->getQuestionService()->searchCount($conditions);

        return $this->getQuestionService()->search($conditions, array('createdTime', 'DESC'), 0, $total);
    }

    protected function canBuildWithQuestions($options, $questions)
    {
        $missing = array();

        foreach ($options['counts'] as $type => $needCount) {
            $needCount = intval($needCount);
            if ($needCount == 0) {
                continue;
            }

            if (empty($questions[$type])) {
                $missing[$type] = $needCount;
                continue;
            }
            if ($type == "material") {
                $validatedMaterialQuestionNum = 0;
                foreach ($questions["material"] as $materialQuestion) {
                    if ($materialQuestion['subCount'] > 0) {
                        $validatedMaterialQuestionNum += 1;
                    }
                }
                if ($validatedMaterialQuestionNum < $needCount) {
                    $missing["material"] = $needCount - $validatedMaterialQuestionNum;
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

    protected function createQuestion($testpaper)
    {
        return TestpaperPatternFactory::create($testpaper['pattern']);
    }

    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
