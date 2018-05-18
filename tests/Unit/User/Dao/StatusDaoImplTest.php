<?php

namespace Tests\Unit\User\Dao;

use Biz\BaseTestCase;

class StatusDaoImplTest extends BaseTestCase
{
    public function testDeleteByCourseIdAndTypeAndObject()
    {
        $this->getStatusDao()->create(
            array(
                'userId' => 33,
                'type' => 'course',
                'courseId' => 123,
                'objectType' => 'courseObject',
                'objectId' => 111222,
                'message' => 'message',
                'properties' => array('id' => 123),
            )
        );

        $this->getStatusDao()->deleteByCourseIdAndTypeAndObject(
            123, 'course', 'courseObject', 111224
        );

        $result = $this->getStatusDao()->findByCourseId(123);
        $this->assertEquals(1, count($result));

        $this->getStatusDao()->deleteByCourseIdAndTypeAndObject(
            123, 'course', 'courseObject', 111222
        );
        $result = $this->getStatusDao()->findByCourseId(123);
        $this->assertEquals(0, count($result));
    }

    private function getStatusDao()
    {
        return $this->createDao('User:StatusDao');
    }
}
