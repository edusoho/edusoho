<?php

namespace Biz\Testpaper\Pattern;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\Biz;

class QuestionTypePattern implements TestpaperPatternInterface
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function getTestpaperQuestions($testpaper, $options)
    {
        $questions = $this->getQuestions($options);
        shuffle($questions);
        $typedQuestions = ArrayToolkit::group($questions, 'type');

        $canBuildResult = $this->canBuildWithQuestions($options, $typedQuestions);
        if ($canBuildResult['status'] == 'no') {
            return array('status' => 'error', 'missing' => $canBuildResult['missing']);
        }

        $items = array();
        foreach ($options['counts'] as $type => $needCount) {
            $needCount = intval($needCount);
            if ($needCount == 0) {
                continue;
            }

            if ($options['mode'] == 'difficulty') {
                $difficultiedQuestions = ArrayToolkit::group($typedQuestions[$type], 'difficulty');

                // 按难度百分比选取Question
                $selectedQuestions = $this->selectQuestionsWithDifficultlyPercentage($difficultiedQuestions, $needCount, $options['percentages']);

                // 选择的Question不足的话，补足
                $selectedQuestions = $this->fillQuestionsToNeedCount($selectedQuestions, $typedQuestions[$type], $needCount);

                $itemsOfType = $this->convertQuestionsToItems($testpaper, $selectedQuestions, $needCount, $options);
            } else {
                $itemsOfType = $this->convertQuestionsToItems($testpaper, $typedQuestions[$type], $needCount, $options);
            }
            $items = array_merge($items, $itemsOfType);
        }

        return array('status' => 'ok', 'items' => $items);
    }

    public function canBuild($options)
    {
        $questions = $this->getQuestions($options);
        $typedQuestions = ArrayToolkit::group($questions, 'type');

        return $this->canBuildWithQuestions($options, $typedQuestions);
    }

    protected function fillQuestionsToNeedCount($selectedQuestions, $allQuestions, $needCount)
    {
        $indexedQuestions = ArrayToolkit::index($allQuestions, 'id');
        foreach ($selectedQuestions as $question) {
            unset($indexedQuestions[$question['id']]);
        }

        if (count($selectedQuestions) < $needCount) {
            $stillNeedCount = $needCount - count($selectedQuestions);
        } else {
            $stillNeedCount = 0;
        }

        if ($stillNeedCount) {
            $questions = array_slice(array_values($indexedQuestions), 0, $stillNeedCount);
            $selectedQuestions = array_merge($selectedQuestions, $questions);
        }

        return $selectedQuestions;
    }

    protected function selectQuestionsWithDifficultlyPercentage($difficultiedQuestions, $needCount, $percentages)
    {
        $selectedQuestions = array();
        foreach ($percentages as $difficulty => $percentage) {
            $subNeedCount = intval($needCount * $percentage / 100);
            if ($subNeedCount == 0) {
                continue;
            }

            if (!empty($difficultiedQuestions[$difficulty])) {
                $questions = array_slice($difficultiedQuestions[$difficulty], 0, $subNeedCount);
                $selectedQuestions = array_merge($selectedQuestions, $questions);
            }
        }

        return $selectedQuestions;
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
            if ($type == 'material') {
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
        $conditions = array(
            'parentId' => 0,
            'courseSetId' => $options['courseSetId'],
        );

        //兼容course1.0 start
        if (!empty($options['ranges']['start'])) {
            $conditions['lessonIdGT'] = $options['ranges']['start'];
        }

        if (!empty($options['ranges']['end'])) {
            $conditions['lessonIdLT'] = $options['ranges']['end'];
        }
        //兼容course1.0 end

        if (!empty($options['ranges']['courseId'])) {
            $conditions['courseId'] = $options['ranges']['courseId'];
        }

        if (!empty($options['ranges']['lessonId'])) {
            $conditions['lessonId'] = $options['ranges']['lessonId'];
        }

        $total = $this->getQuestionService()->searchCount($conditions);

        return $this->getQuestionService()->search(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $total
        );
    }

    protected function convertQuestionsToItems($testpaper, $questions, $count, $options)
    {
        $items = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = $questions[$i];

            $score = empty($options['scores'][$question['type']]) ? 0 : floatval($options['scores'][$question['type']]);
            $missScore = empty($options['missScores'][$question['type']]) ? 0 : floatval($options['missScores'][$question['type']]);

            $items[] = $this->makeItem($testpaper, $question, $score, $missScore);

            if ($question['subCount'] > 0) {
                $subQuestions = $this->getQuestionService()->findQuestionsByParentId($question['id'], 0, $question['subCount']);
                foreach ($subQuestions as $subQuestion) {
                    $missScore = empty($options['missScores'][$subQuestion['type']]) ? 0 : $options['missScores'][$subQuestion['type']];
                    $items[] = $this->makeItem($testpaper, $subQuestion, $score, $missScore);
                }
            }
        }

        return $items;
    }

    protected function makeItem($testpaper, $question, $score, $missScore)
    {
        return array(
            'testId' => $testpaper['id'],
            'questionId' => $question['id'],
            'questionType' => $question['type'],
            'parentId' => $question['parentId'],
            'score' => $score,
            'missScore' => $missScore,
        );
    }

    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }
}
