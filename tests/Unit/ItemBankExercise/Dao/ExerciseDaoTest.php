<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use AppBundle\Common\ArrayToolkit;
use Tests\Unit\Base\BaseDaoTestCase;

class ExerciseDaoTest extends BaseDaoTestCase
{
    public function testGetByQuestionBankId()
    {
        $expectedResult = $this->mockDataObject(['questionBankId' => 1]);
        $result = $this->getDao()->getByQuestionBankId(1);

        $this->assertArrayEquals($expectedResult, $result, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $this->mockDataObject(['id' => 1]);
        $this->mockDataObject(['id' => 2]);
        $res = $this->getDao()->findByIds([1, 2]);

        $this->assertEquals('2', count($res));
        $this->assertEquals([1, 2], ArrayToolkit::column($res, 'id'));
    }

    public function testSearchOrderByStudentNumAndLastDays()
    {
        $this->batchCreateExercise();
        $this->mockExerciseMembers();

        $exercises = $this->getDao()->searchOrderByStudentNumAndLastDays([], 1, 0, 3);

        $this->assertEquals(3, $exercises[0]['id']);
        $this->assertEquals(2, $exercises[1]['id']);
        $this->assertEquals(1, $exercises[2]['id']);
    }

    public function testSearchOrderByRatingAndLastDays()
    {
        $this->batchCreateExercise();
        $this->mockReviews();

        $exercises = $this->getDao()->searchOrderByRatingAndLastDays([], 1, 0, 3);

        $this->assertEquals(3, $exercises[0]['id']);
        $this->assertEquals(2, $exercises[1]['id']);
        $this->assertEquals(1, $exercises[2]['id']);
    }

    protected function batchCreateExercise()
    {
        return $this->getDao()->batchCreate(
            [
                [
                    'id' => 1,
                    'title' => 'test1',
                    'questionBankId' => 1,
                    'categoryId' => 1,
                    'seq' => 1,
                ],
                [
                    'id' => 2,
                    'title' => 'test2',
                    'questionBankId' => 2,
                    'categoryId' => 1,
                    'seq' => 2,
                ],
                [
                    'id' => 3,
                    'title' => 'test3',
                    'questionBankId' => 3,
                    'categoryId' => 2,
                    'seq' => 3,
                ],
            ]
        );
    }

    protected function mockExerciseMembers()
    {
        $this->getExerciseMemberDao()->batchCreate([
            ['exerciseId' => 3, 'userId' => 1, 'role' => 'student'],
            ['exerciseId' => 3, 'userId' => 2, 'role' => 'student'],
            ['exerciseId' => 2, 'userId' => 2, 'role' => 'student'],
        ]);
    }

    protected function mockReviews()
    {
        $this->getReviewDao()->create([
            'userId' => 1,
            'targetType' => 'item_bank_exercise',
            'targetId' => 3,
            'rating' => 3,
        ]);

        $this->getReviewDao()->create([
            'userId' => 1,
            'targetType' => 'item_bank_exercise',
            'targetId' => 3,
            'rating' => 4,
        ]);

        $this->getReviewDao()->create([
            'userId' => 1,
            'targetType' => 'item_bank_exercise',
            'targetId' => 2,
            'rating' => 3,
        ]);
    }

    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }

    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }

    protected function getDefaultMockFields()
    {
        return [
            'title' => 'a',
            'questionBankId' => 1,
        ];
    }
}
