<?php

namespace Biz\WrongBook\Service\Impl;

use Biz\BaseService;
use Biz\WrongBook\Dao\WrongQuestionBookExerciseDao;
use Biz\WrongBook\Service\WrongBookPractiseService;
use Codeages\Biz\Framework\Event\Event;

class WrongBookPractiseServiceImpl extends BaseService implements WrongBookPractiseService
{
    public function createExercise($fields)
    {
        $exercise = $this->getValidator()->validate($fields, [
            'answer_scene_id' => ['required', 'integer', ['min', 0]],
            'assessment_id' => ['integer', ['min', 0]],
            'regulation' => [],
            'user_id' => ['integer', ['min', 0]],
        ]);

        $exercise = $this->getWrongQuestionBookExerciseDao()->create($exercise);

        $this->dispatchEvent('wrong_question.create', new Event($exercise, []));

        return $exercise;
    }

    public function updateExercise()
    {
    }

    /**
     * @return WrongQuestionBookExerciseDao
     */
    protected function getWrongQuestionBookExerciseDao()
    {
        return $this->createDao('WrongBook:WrongQuestionBookExerciseDao');
    }
}
