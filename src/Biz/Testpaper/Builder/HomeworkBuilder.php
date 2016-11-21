<?php
namespace Biz\Testpaper\Builder;

use Biz\Factory;
use Topxia\Common\ArrayToolkit;
use Biz\Testpaper\Builder\TestpaperLibBuilder;
use Topxia\Common\Exception\InvalidArgumentException;

class HomeworkBuilder extends Factory implements TestpaperLibBuilder
{
    public function build($fields)
    {
        if (empty($fields['questionIds'])) {
            throw new \InvalidArgumentException('homework field is invalid');
        }
        $questionIds = $fields['questionIds'];
        $fields      = $this->filterFields($fields);
        $homework    = $this->getTestpaperService()->createTestpaper($fields);

        $this->createQuestionItems($homework['id'], $questionIds);

        return $homework;
    }

    public function canBuild($options)
    {
        $questions      = $this->getQuestions($options);
        $typedQuestions = ArrayToolkit::group($questions, 'type');
        return $this->canBuildWithQuestions($options, $typedQuestions);
    }

    public function showTestItems($resultId)
    {
        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($resultId);
        $items          = $this->getTestpaperService()->findItemsByTestId($homeworkResult['testId']);

        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($homeworkResult['id']);
        $itemResults = ArrayToolkit::index($itemResults, 'questionId');

        $questionIds = ArrayToolkit::column($items, 'questionId');
        $questions   = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $formatQuestions = array();
        foreach ($items as $questionId => $item) {
            $question = empty($questions[$questionId]) ? array('isDeleted' => true) : $questions[$questionId];

            if (!empty($itemResults[$questionId])) {
                $question['testResult'] = $itemResults[$questionId];
            }

            $question['score']     = $item['score'];
            $question['seq']       = $item['seq'];
            $question['missScore'] = $item['missScore'];

            $questionConfig       = $this->getQuestionService()->getQuestionConfig($item['questionType']);
            $question['template'] = $questionConfig->getTemplate('do');

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
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId'))) {
            throw new \InvalidArgumentException('homework field is invalid');
        }

        $filtedFields = array();

        $filtedFields['courseId'] = $fields['courseId'];
        $filtedFields['lessonId'] = $fields['lessonId'];
        $filtedFields['type']     = 'homework';

        if (!empty($fields['name'])) {
            $filtedFields['name'] = $fields['name'];
        }

        if (!empty($fields['description'])) {
            $filtedFields['description'] = $fields['description'];
        }

        if (!empty($fields['passedCondition'])) {
            $filtedFields['passedCondition'] = $fields['correctPercent'];
        }

        if (!empty($fields['questionIds'])) {
            $filtedFields['itemCount'] = count($fields['questionIds']);
        }

        $filtedFields['status']  = 'open';
        $filtedFields['pattern'] = 'questionType';

        return $filtedFields;
    }

    protected function createQuestionItems($homeworkId, $questionIds)
    {
        $homeworkItems = array();
        $index         = 1;

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        foreach ($questions as $key => $question) {
            $questionSubs = $this->getQuestionService()->findQuestionsByParentId($question['id']);

            $items['seq']          = $index++;
            $items['questionId']   = $question['id'];
            $items['questionType'] = $question['type'];
            $items['testId']       = $homeworkId;
            $items['parentId']     = 0;
            $homeworkItems[]       = $this->getTestpaperService()->createItem($items);

            if (!empty($questionSubs)) {
                $i = 1;

                foreach ($questionSubs as $key => $questionSub) {
                    $items['seq']          = $i++;
                    $items['questionId']   = $questionSub['id'];
                    $items['questionType'] = $questionSub['type'];
                    $items['testId']       = $homeworkId;
                    $items['parentId']     = $questionSub['parentId'];
                    $homeworkItems[]       = $this->getTestpaperService()->createItem($items);
                }
            }
        }

        return $homeworkItems;
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

    protected function getQuestions($options)
    {
        $conditions        = array();
        $options['ranges'] = array_filter($options['ranges']);

        $conditions['courseId'] = $options['courseId'];

        if (!empty($options['ranges'])) {
            $conditions['lessonIds'] = $options['ranges'];
        }

        $conditions['parentId'] = 0;

        $total = $this->getQuestionService()->searchCount($conditions);

        return $this->getQuestionService()->search($conditions, array('createdTime', 'DESC'), 0, $total);
    }

    protected function makeItem($homeworkId, $question)
    {
        return array(
            'testId'       => $homeworkId,
            'questionId'   => $question['id'],
            'questionType' => $question['type'],
            'parentId'     => $question['parentId'],
            'score'        => $question['score'],
            'missScore'    => 0
        );
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
