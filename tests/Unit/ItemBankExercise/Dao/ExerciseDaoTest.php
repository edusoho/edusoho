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
        $this->assertEquals([1,2],ArrayToolkit::column($res,'id'));
    }

    protected function getDefaultMockFields()
    {
        return [
            'title' => 'a',
            'questionBankId' => 1,
        ];
    }
}