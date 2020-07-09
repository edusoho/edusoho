<?php

namespace Tests\Unit\ItemBankExercise\Accessor;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Accessor\JoinExerciseAccessor;

class JoinExerciseAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $accessor = new JoinExerciseAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'published',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertNull($result);
    }

    public function testAccess_whenExerciseEmpty_thenReturnError()
    {
        $accessor = new JoinExerciseAccessor($this->getBiz());
        $result = $accessor->access([]);

        $this->assertEquals($result['code'], 'item_bank_exercise.not_found');
    }

    public function testAccess_whenStatusEqDraft_thenReturnError()
    {
        $accessor = new JoinExerciseAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'draft',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertEquals($result['code'], 'item_bank_exercise.unpublished');
    }

    public function testAccess_whenStatusEqClosed_thenReturnError()
    {
        $accessor = new JoinExerciseAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'closed',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertEquals($result['code'], 'item_bank_exercise.closed');
    }

    public function testAccess_whenJoinEnableEq0_thenReturnError()
    {
        $accessor = new JoinExerciseAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'published',
            'expiryMode' => 'forever',
            'joinEnable' => 0,
        ]);

        $this->assertEquals($result['code'], 'item_bank_exercise.not_join_enable');
    }

    public function testAccess_whenIsExpired_thenReturnError()
    {
        $accessor = new JoinExerciseAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'published',
            'expiryMode' => 'date',
            'joinEnable' => 1,
            'expiryStartDate' => 1,
            'expiryEndDate' => 1,
        ]);

        $this->assertEquals($result['code'], 'item_bank_exercise.expired');
    }
}
