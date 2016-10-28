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
        $questionIds = explode(',', $fields['excludeIds']);

        $fields   = $this->filterFields($fields);
        $homework = $this->getTestpaperService()->createTestpaper($fields);

        $this->createQuestionItems($homework['id'], $questionIds);

        return $homework;
    }

    public function canBuild($options)
    {
        $questions      = $this->getQuestions($options);
        $typedQuestions = ArrayToolkit::group($questions, 'type');
        return $this->canBuildWithQuestions($options, $typedQuestions);
    }

    protected function filterFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId', 'excludeIds'))) {
            throw new \InvalidArgumentException('Testpaper field is invalid');
        }

        $filtedFields = array();

        $filtedFields['courseId']        = $fields['courseId'];
        $filtedFields['lessonId']        = $fields['lessonId'];
        $filtedFields['type']            = 'homework';
        $filtedFields['passedCondition'] = empty($fields['correctPercent']) ? array() : $fields['correctPercent'];
        $filtedFields['description']     = empty($fields['description']) ? '' : $fields['description'];

        $excludeIds              = explode(',', $fields['excludeIds']);
        $excludeIds['itemCount'] = count($excludeIds);

        $filtedFields['status']  = 'open';
        $filtedFields['pattern'] = 'questionType';

        return $filtedFields;
    }

    protected function createQuestionItems($homeworkId, $questionIds)
    {
        if (empty($questionIds)) {
            return array();
        }

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $homeworkItems = array();
        $seq           = 1;

        foreach ($questions as $question) {
            $questionsSub = $this->getQuestionService()->findQuestionsByParentId($question['id']);

            $item        = $this->makeItem($homeworkId, $question);
            $item['seq'] = $seq++;

            $homeworkItems[] = $this->getTestpaperService()->createTestpaperItem($item);

            if (!empty($questionsSub)) {
                $i = 1;

                foreach ($questionsSub as $key => $question) {
                    $item            = $this->makeItem($homeworkId, $question);
                    $item['seq']     = $seq++;
                    $homeworkItems[] = $this->getTestpaperService()->createTestpaperItem($item);
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
