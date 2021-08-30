<?php

namespace Tests\Unit\MultiClass\Dao;

use Biz\BaseTestCase;
use Biz\MultiClass\Dao\MultiClassGroupDao;

class MultiClassGroupDaoTest extends BaseTestCase
{
    public function testFindByIds()
    {
        $this->mockGroups();
        $result = $this->getMultiClassGroupDao()->findByIds([1, 2, 4]);

        $this->assertEquals(2, count($result));
    }

    public function testFindGroupsByMultiClassId()
    {
        $this->mockGroups();
        $result = $this->getMultiClassGroupDao()->findGroupsByMultiClassId(1);

        $this->assertEquals(2, count($result));
    }

    protected function mockGroups()
    {
        return $this->getMultiClassGroupDao()->batchCreate([
            ['id' => 1, 'name' => '分组1', 'assistant_id' => 10, 'multi_class_id' => 1, 'course_id' => 1, 'student_num' => 3],
            ['id' => 2, 'name' => '分组2', 'assistant_id' => 11, 'multi_class_id' => 2, 'course_id' => 2, 'student_num' => 5],
            ['id' => 3, 'name' => '分组3', 'assistant_id' => 12, 'multi_class_id' => 1, 'course_id' => 1, 'student_num' => 9],
        ]);
    }

    /**
     * @return MultiClassGroupDao
     */
    protected function getMultiClassGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassGroupDao');
    }
}
