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
        $questions     = $this->getQuestions($options);
        $questionCount = count($questions);

        if ($questionCount < $options['itemCount']) {
            $lessNum = $options['itemCount'] - $questionCount;
            return array('status' => 'no', 'lessNum' => $lessNum);
        } else {
            return array('status' => 'yes');
        }
    }

    public function filterFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId'))) {
            throw new \InvalidArgumentException('exercise field is invalid');
        }

        $filtedFields = array();

        $filtedFields['itemCount'] = $fields['itemCount'];
        $filtedFields['courseId']  = $fields['courseId'];
        $filtedFields['lessonId']  = $fields['lessonId'];
        $filtedFields['type']      = 'exercise';
        $filtedFields['status']    = 'open';
        $filtedFields['pattern']   = 'questionType';
        $filtedFields['copyId']    = empty($fields['copyId']) ? 0 : $fields['copyId'];
        $filtedFields['metas']     = empty($fields['metas']) ? array() : $fields['metas'];
        $filtedFields['name']      = empty($fields['name']) ? '' : $fields['name'];

        $filtedFields['metas']['questionTypes'] = empty($fields['questionTypes']) ? array() : $fields['questionTypes'];
        $filtedFields['metas']['difficulty']    = empty($fields['difficulty']) ? '' : $fields['difficulty'];
        $filtedFields['metas']['range']         = empty($fields['range']) ? 'course' : $fields['range'];

        $filtedFields['passedCondition'] = array(0);

        return $filtedFields;
    }

    protected function getQuestions($fields)
    {
        $conditions = array();

        if (!empty($fields['difficulty'])) {
            $conditions['difficulty'] = $fields['difficulty'];
        }

        if ($fields['range'] == 'lesson') {
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
