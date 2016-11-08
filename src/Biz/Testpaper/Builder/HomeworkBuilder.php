<?php
namespace Biz\Testpaper\Builder;

use Biz\Factory;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
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

    public function filterFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId'))) {
            throw new \InvalidArgumentException('homework field is invalid');
        }

        $filtedFields = array();

        $filtedFields['courseId']        = $fields['courseId'];
        $filtedFields['lessonId']        = $fields['lessonId'];
        $filtedFields['type']            = 'homework';
        $filtedFields['passedCondition'] = empty($fields['passedCondition']) ? array() : $fields['correctPercent'];
        $filtedFields['description']     = empty($fields['description']) ? '' : $fields['description'];
        $filtedFields['name']            = empty($fields['name']) ? '' : $fields['name'];

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
            $questionsSubs = $this->getQuestionService()->findQuestionsByParentId($question['id']);

            $items['seq']          = $index++;
            $items['questionId']   = $question['id'];
            $items['questionType'] = $question['type'];
            $items['testId']       = $homeworkId;
            $items['parentId']     = 0;
            $homeworkItems[]       = $this->getTestpaperService()->createTestpaperItem($items);

            if (!empty($questionsSub)) {
                $i = 1;

                foreach ($questionsSubs as $key => $questionSub) {
                    $items['seq']          = $i++;
                    $items['questionId']   = $questionSub['id'];
                    $items['questionType'] = $questionSub['type'];
                    $items['testId']       = $homeworkId;
                    $items['parentId']     = $questionSub['parentId'];
                    $homeworkItems[]       = $this->getTestpaperService()->createTestpaperItem($items);
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
        if (!empty($options['ranges'])) {
            $conditions['targets'] = $options['ranges'];
        } else {
            $conditions['targetPrefix'] = 'course-'.$options['courseId'];
        }

        $conditions['parentId'] = 0;

        $total = $this->getQuestionService()->searchQuestionsCount($conditions);

        return $this->getQuestionService()->searchQuestions($conditions, array('createdTime', 'DESC'), 0, $total);
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
        return ServiceKernel::instance()->createService('Question.QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }
}
