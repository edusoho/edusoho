<?php
namespace Topxia\Service\Testpaper\Builder;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Testpaper\TestpaperBuilder;

class QuestionTypeTestpaperBuilder extends BaseService implements TestpaperBuilder
{

    public function build($testpaper, $options)
    {
        $questions = $this->getQuestions($options);
        $typedQuestions = $this->buildQuestionsGroupByType($questions);

        $canBuildResult = $this->canBuildWithQuestions($options, $typedQuestions);
        if ($canBuildResult['status'] == 'no') {
            return array('status' => 'error', 'missing' => $canBuildResult['missing']);
        }

        $typedQuestions = $this->buildQuestionsGroupByType($typedQuestions);

        $items = array();
        foreach ($options['counts'] as $type => $needCount) {
            $needCount = intval($needCount);
            if ($needCount == 0) {
                continue;
            }

            if ($options['mode'] == 'difficulty') {
                $difficultiedQuestions = $this->buildQuestionsGroupByDifficulty();

            } else {
                $itemsOfType = $this->convertQuestionsToItems($testpaper, $typedQuestions[$type], $needCount);
                $items = array_merge($items, $itemsOfType);
            }
        }

        return $items;
    }

    public function canBuild($options)
    {
        $questions = $this->getQuestions($options);
        $typedQuestions = $this->buildQuestionsGroupByType($questions);
        return $this->canBuildWithQuestions($options, $typedQuestions);
    }

    private function canBuildWithQuestions($options, $questions)
    {
        $missing = array();

        foreach ($options['counts'] as $type => $needCount) {
            $needCount = intval($needCount);
            if ($needCount == 0) {
                continue;
            }

            if (empty($typedQuestions[$type])) {
                $missing[$type] = $needCount;
                continue;
            }

            if (count($typedQuestions[$type]) < $needCount) {
                $missing[$type] = $needCount - count($typedQuestions[$type]);
            }
        }

        if (empty($missing)) {
            return array('status' => 'yes');
        }

        return array('status' => 'no', 'missing' => $missing);
    }

    private function buildQuestionsGroupByType($questions)
    {
        $typeToids = array();
        foreach ($questions as $question) {
            if (empty($typeToids[$question['type']])) {
                $typeToids[$question['type']] = array();
            }
            $typeToids[$question['type']][] = $question;
        }

        return $typeToids;
    }

    private function getQuestions($options)
    {
        $conditions = array();

        if (!empty($options['ranges'])) {
            $conditions['targets'] = $options['ranges'];
        } else {
            $conditions['targetPrefix'] = $options['target'];
        }

        $total = $this->getQuestionService()->searchQuestionsCount($conditions);

        return $this->getQuestionService()->searchQuestions($conditions, array('createdTime', 'DESC'), 0, $total);
    }

    private function convertQuestionsToItems($testpaper, $questions, $count)
    {
        $items = array();
        for ($i=0; $i<$count; $i++) {
            $question = $questions[$i];
            $items[] = $this->makeItem($testpaper, $question);
            if ($question['subCount'] > 0) {
                $subQuestions = $this->getQuestionService()->findQuestionsByParentId($question['id'], 0, $question['subCount']);
                foreach ($subQuestions as $subQuestion) {
                    $items[] = $this->makeItem($testpaper, $subQuestion);
                }
            }
        }
        return $items;
    }

    private function makeItem($testpaper, $question)
    {
        return array(
            'testId' => $testpaper['id'],
            'seq' => $seq,
            'questionId' => $question['id'],
            'questionType' => $question['type'],
            'parentId' => $question['parentId'],
            'score' => $options['scores'][$type],
            'missScore' => $testpaper['missScore'],
        );
    }

    private function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }

}