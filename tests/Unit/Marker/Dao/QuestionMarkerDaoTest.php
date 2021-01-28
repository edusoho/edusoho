<?php

namespace Tests\Unit\Marker\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class QuestionMarkerDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('seq' => 3, 'markerId' => 3, 'stem' => 'stem'));
        $expected[] = $this->mockDataObject(array('difficulty' => 'hard', 'type' => 'type2', 'questionId' => 3));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('seq' => 3),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('markerId' => 3),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('stem' => 'tem'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('difficulty' => 'hard'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('type' => 'type2'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('questionId' => 3),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('ids' => array(3)),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testGetMaxSeqByMarkerId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('seq' => 3));
        $result = $this->getDao()->getMaxSeqByMarkerId(2);
        $this->assertEquals(3, $result['seq']);
    }

    public function testMerge()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->merge(2, 3, 1);
        $result = $this->getDao()->get(1);
        $this->assertEquals(3, $result['seq']);
    }

    public function testFindByIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('seq' => 3));
        $result = $this->getDao()->findByIds(array(1, 2));
        $this->assertArrayEquals($expected[1], $result[1], $this->getCompareKeys());
    }

    public function testFindByMarkerId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('seq' => 1));
        $result = $this->getDao()->findByMarkerId(2);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByMarkerIds()
    {
        $result = $this->getDao()->findByMarkerIds(array());
        $this->assertEquals(array(), $result);

        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('seq' => 1));
        $result = $this->getDao()->findByMarkerIds(array(2));
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByQuestionId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('questionId' => 1));
        $result = $this->getDao()->findByQuestionId(1);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testWaveSeqBehind()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->waveSeqBehind(2, 1);
        $result = $this->getDao()->get(1);
        $this->assertEquals(3, $result['seq']);
    }

    public function testWaveSeqForward()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->waveSeqForward(2, 1);
        $result = $this->getDao()->get(1);
        $this->assertEquals(1, $result['seq']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'markerId' => 2,
            'questionId' => 2,
            'seq' => 2,
            'type' => 'type1',
            'difficulty' => 'normal',
        );
    }
}
