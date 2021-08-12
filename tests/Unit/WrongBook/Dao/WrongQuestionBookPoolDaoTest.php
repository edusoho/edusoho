<?php

namespace Tests\Unit\WrongBook\Dao;

use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;

class WrongQuestionBookPoolDaoTest extends BaseTestCase
{
    public function testGetPoolByUserIdAndTargetTypeAndTargetId()
    {
        $this->mockWrongQuestionBookPool();
        $wrongQuestionBookPool = $this->getWrongQuestionBookPoolDao()->getPoolByUserIdAndTargetTypeAndTargetId(1, 'course', 1);
        $this->assertEquals('1', $wrongQuestionBookPool['item_num']);
    }

    public function testGetPoolByFieldsGroupByTargetType()
    {
        $this->mockWrongQuestionBookPool();
        $wrongQuestionBookPool = $this->getWrongQuestionBookPoolDao()->getPoolByFieldsGroupByTargetType(['user_id' => 1]);
        $this->assertEquals(3, $wrongQuestionBookPool[0]['sum_wrong_num']);
    }

    public function testSearchPoolByConditions()
    {
        $this->mockWrongQuestionBookPool();
        $this->createCourse(2, 1);
        $wrongQuestionBookPool = $this->getWrongQuestionBookPoolDao()->searchPoolByConditions(['target_type' => 'course'], [], 0, PHP_INT_MAX);
        $this->assertCount(2, $wrongQuestionBookPool);
    }

    public function testCountPoolByConditions()
    {
        $this->mockWrongQuestionBookPool();
        $this->createCourse(2, 1);
        $wrongQuestionBookPoolCount = $this->getWrongQuestionBookPoolDao()->countPoolByConditions(['target_type' => 'course']);
        $this->assertEquals(2, $wrongQuestionBookPoolCount);
    }

    protected function createCourse($courseId, $courseSetId)
    {
        $course = [
            'id' => $courseId,
            'title' => 'course title',
            'courseSetId' => $courseSetId,
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => 1,
            'courseType' => 'normal',
        ];

        $this->getCourseService()->createCourse($course);
    }

    protected function mockWrongQuestionBookPool()
    {
        $pool = [
            ['user_id' => 1,
               'item_num' => 1,
               'target_type' => 'course',
               'target_id' => 1,
            ],
            ['user_id' => 1,
                'item_num' => 2,
                'target_type' => 'course',
                'target_id' => 2,
            ],
        ];

        return $this->getWrongQuestionBookPoolDao()->batchCreate($pool);
    }

    /**
     * @return WrongQuestionBookPoolDao
     */
    protected function getWrongQuestionBookPoolDao()
    {
        return $this->createDao('WrongBook:WrongQuestionBookPoolDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
