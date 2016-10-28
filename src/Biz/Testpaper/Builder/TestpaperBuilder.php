<?php
namespace Biz\Testpaper\Builder;

use Biz\Factory;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\Exception\RuntimeException;
use Biz\Testpaper\Builder\TestpaperLibBuilder;
use Biz\Testpaper\Pattern\TestpaperPatternFactory;
use Topxia\Common\Exception\InvalidArgumentException;
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
            throw new \RuntimeException("Build testpaper #{$id} items error.");
        }

        $this->createQuestionItems($result['items']);

        return $testpaper;
    }

    protected function filterFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name', 'pattern', 'courseId'))) {
            throw new \InvalidArgumentException('Testpaper field is invalid');
        }

        $filtedFields = array();

        $filtedFields['name']        = $fields['name'];
        $filtedFields['pattern']     = $fields['pattern'];
        $filtedFields['description'] = empty($fields['description']) ? '' : $fields['description'];
        $filtedFields['courseId']    = $fields['courseId'];
        $filtedFields['lessonId']    = empty($fields['lessonId']) ? 0 : $fields['lessonId'];
        $filtedFields['type']        = 'testpaper';

        $filtedFields['metas']  = empty($fields['metas']) ? array() : $fields['metas'];
        $filtedFields['status'] = 'draft';

        $filtedFields['limitedTime']     = empty($fields['limitedTime']) ? 0 : (int) $fields['limitedTime'];
        $filtedFields['passedCondition'] = empty($fields['passedCondition']) ? array(0) : array($fields['passedCondition']);

        $filtedFields['copyId'] = empty($fields['copyId']) ? 0 : $fields['copyId'];

        $filtedFields['metas']['mode']        = $fields['mode'];
        $filtedFields['metas']['range']       = $fields['range'];
        $filtedFields['metas']['ranges']      = $fields['ranges'];
        $filtedFields['metas']['counts']      = $fields['counts'];
        $filtedFields['metas']['scores']      = $fields['scores'];
        $filtedFields['metas']['missScores']  = $fields['missScores'];
        $filtedFields['metas']['percentages'] = $fields['percentages'];

        return $filtedFields;
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

            $testpaperItems[] = $this->getTestpaperService()->createTestpaperItem($item);
        }

        return $testpaperItems;
    }

    public function canBuild($options)
    {
        $questions      = $this->getQuestions($options);
        $typedQuestions = ArrayToolkit::group($questions, 'type');
        return $this->canBuildWithQuestions($options, $typedQuestions);
    }

    protected function getQuestions($options)
    {
        $conditions        = array();
        $options['ranges'] = array_filter($options['ranges']);
        if (!empty($options['ranges'])) {
            $conditions['targets'] = $options['ranges'];
        } else {
            $conditions['targetPrefix'] = 'course-'.$options['courseId'];
        }

        $conditions['parentId'] = 0;

        $total = $this->getQuestionService()->searchQuestionsCount($conditions);

        return $this->getQuestionService()->searchQuestions($conditions, array('createdTime', 'DESC'), 0, $total);
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
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
