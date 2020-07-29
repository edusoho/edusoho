<?php

namespace Tests\Unit\ItemBankExercise\Accessor;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Accessor\LearnExerciseAccessor;

class LearnExerciseAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $accessor = new LearnExerciseAccessor($this->getBiz());
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
        $accessor = new LearnExerciseAccessor($this->getBiz());
        $result = $accessor->access([]);

        $this->assertEquals($result['code'], 'item_bank_exercise.not_found');
    }

    public function testAccess_whenStatusEqDraft_thenReturnError()
    {
        $accessor = new LearnExerciseAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'draft',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertEquals($result['code'], 'item_bank_exercise.unpublished');
    }

    public function testAccess_whenIsNotArriving_thenReturnError()
    {
        $accessor = new LearnExerciseAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'draft',
            'expiryMode' => 'date',
            'expiryStartDate' => time() + 100,
            'joinEnable' => 1,
        ]);

        $this->assertEquals($result['code'], 'item_bank_exercise.unpublished');
    }
}
