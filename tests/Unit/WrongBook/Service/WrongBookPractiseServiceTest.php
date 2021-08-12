<?php

namespace Tests\Unit\WrongBook\Service;

use Biz\BaseTestCase;
use Biz\WrongBook\Service\WrongBookPractiseService;

class WrongBookPractiseServiceTest extends BaseTestCase
{
    public function testCreateExercise()
    {
        $exercise = $this->mockExercise();
        $res = $this->getWrongBookPractiseService()->createExercise($exercise);
        self::assertEquals($exercise['answer_scene_id'], $res['answer_scene_id']);
        self::assertEquals($exercise['user_id'], $res['user_id']);
    }

    public function testUpdateExercise()
    {
        $exercise = $this->mockExercise(['user_id' => 2]);
        $res = $this->getWrongBookPractiseService()->createExercise($exercise);
        self::assertEquals(2, $res['user_id']);

        $updated = $this->getWrongBookPractiseService()->updateExercise($res['id'], ['user_id' => 3]);
        self::assertEquals(3, $updated['user_id']);
    }

    protected function mockExercise($customFields = [])
    {
        return array_merge([
            'answer_scene_id' => 1,
            'assessment_id' => 1,
            'regulation' => [],
            'user_id' => 1,
        ], $customFields);
    }

    /**
     * @return WrongBookPractiseService
     */
    protected function getWrongBookPractiseService()
    {
        return $this->createService('WrongBook:WrongBookPractiseService');
    }
}
