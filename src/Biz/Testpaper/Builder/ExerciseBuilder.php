<?php
namespace Biz\Testpaper\Builder;

use Biz\Factory;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Biz\Testpaper\Builder\TestpaperLibBuilder;
use Topxia\Common\Exception\InvalidArgumentException;

class ExerciseBuilder extends Factory implements TestpaperLibBuilder
{
    public function build($fields)
    {
        $fields = $this->filterFields($fields);
        return $this->getTestpaperService()->createTestpaper($fields);
    }

    public function canBuild($options)
    {
        $questions      = $this->getQuestions($options);
        $typedQuestions = ArrayToolkit::group($questions, 'type');
        return $this->canBuildWithQuestions($options, $typedQuestions);
    }

    protected function filterFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId'))) {
            throw new \InvalidArgumentException('Testpaper field is invalid');
        }

        $filtedFields = array();

        $filtedFields['itemCount'] = $fields['questionCount'];
        $filtedFields['courseId']  = $fields['courseId'];
        $filtedFields['lessonId']  = $fields['lessonId'];
        $filtedFields['type']      = 'exercise';
        $filtedFields['status']    = 'open';
        $filtedFields['pattern']   = 'questionType';
        $filtedFields['copyId']    = empty($fields['copyId']) ? 0 : $fields['copyId'];
        $filtedFields['metas']     = empty($fields['metas']) ? array() : $fields['metas'];

        $filtedFields['metas']['questionTypes'] = empty($fields['questionTypes']) ? array() : $fields['questionTypes'];
        $filtedFields['metas']['difficulty']    = empty($fields['difficulty']) ? '' : $fields['difficulty'];
        $filtedFields['metas']['range']         = empty($fields['source']) ? 'course' : $fields['source'];

        return $filtedFields;
    }

    protected function canBuildWithQuestions($options, $questions)
    {
        $missing = array();

        /*foreach ($options['counts'] as $type => $needCount) {
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
        }*/

        //if (empty($missing)) {
        return array('status' => 'yes');
        //}

        //return array('status' => 'no', 'missing' => $missing);
    }

    protected function getQuestions($fields)
    {
        $conditions = array();

        if (!empty($fields['difficulty'])) {
            $conditions['difficulty'] = $fields['difficulty'];
        }

        if ($fields['source'] == 'lesson') {
            $conditions['target'] = 'course-'.$fields['courseId'].'/lesson-'.$fields['lessonId'];
        } else {
            $conditions['targetPrefix'] = 'course-'.$fields['courseId'];
        }
        $conditions['types']                      = $fields['questionTypes'];
        $conditions['parentId']                   = 0;
        $conditions['excludeUnvalidatedMaterial'] = $fields['excludeUnvalidatedMaterial'];

        $total = $this->getQuestionService()->searchQuestionsCount($conditions);

        return $this->getQuestionService()->searchQuestions($conditions, array('createdTime', 'DESC'), 0, $total);
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
