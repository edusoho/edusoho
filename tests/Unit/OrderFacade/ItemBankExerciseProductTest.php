<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\OrderFacade\Product\ItemBankExerciseProduct;

class ItemBankExerciseProductTest extends BaseTestCase
{
    public function testValidate()
    {
        $product = new ItemBankExerciseProduct();
        $product->setBiz($this->getBiz());

        $this->mockBiz('ItemBankExercise:ExerciseService', [
            ['functionName' => 'canJoinExercise', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);
        $this->assertEquals(null, $product->validate());
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidate_whenValidateFail_thenThrowException()
    {
        $product = new ItemBankExerciseProduct();
        $product->setBiz($this->getBiz());

        $this->mockBiz('ItemBankExercise:ExerciseService', [
            ['functionName' => 'canJoinExercise', 'returnValue' => ['code' => 'error', 'msg' => 'wrong']],
        ]);
        $product->validate();
    }
}
