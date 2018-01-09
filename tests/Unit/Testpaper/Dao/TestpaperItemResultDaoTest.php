<?php

namespace Tests\Unit\Testpaper\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TestpaperItemResultDaoTest extends BaseDaoTestCase
{
    public function testFindItemResultsByResultId()
    {
        $this->mockDataObject();
        $results = $this->getDao()->findItemResultsByResultId(1, 'testpaper');
        $this->assertEquals(1, count($results));
    }

    public function testAddItemAnswers()
    {
        $answers = array(
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array(),
        );
        $resultsCount = $this->getDao()->addItemAnswers(1, $answers, 1, 1);
        $this->assertEquals(4, $resultsCount);

        $answers = array();
        $result = $this->getDao()->addItemAnswers(1, $answers, 1, 1);
        $this->assertEquals(array(), $result);
    }

    public function testUpdateItemAnswers()
    {
        $answers = array(
            1 => array(),
        );
        $this->getDao()->addItemAnswers(1, $answers, 1, 1);
        $updateAnswers = array(
            1 => array(1, 2, 3),
        );
        $this->getDao()->updateItemAnswers(1, $updateAnswers);
        $results = $this->getDao()->findItemResultsByResultId(1, 'testpaper');
        $first = reset($results);
        $this->assertEquals(array(1, 2, 3), $first['answer']);

        $updateAnswers = array();
        $return = $this->getDao()->updateItemAnswers(1, $updateAnswers);
        $this->assertEquals(array(), $return);
    }

    public function testUpdateItemResults()
    {
        $answers = array(
            1 => array(),
        );
        $this->getDao()->addItemAnswers(1, $answers, 1, 1);
        $testRes = array(
            1 => array('status' => 'noAnswer', 'score' => 3.00),
        );

        $this->getDao()->updateItemResults(1, $testRes);

        $results = $this->getDao()->findItemResultsByResultId(1, 'testpaper');
        $first = reset($results);
        $this->assertEquals(3.00, $first['score']);

        $testRes = array();
        $return = $this->getDao()->updateItemResults(1, $testRes);
        $this->assertEquals(array(), $return);
    }

    public function testUpdateItemEssays()
    {
        $answers = array(
            1 => array(),
        );
        $this->getDao()->addItemAnswers(1, $answers, 1, 1);
        $testRes = array(
            1 => array('status' => 'noAnswer', 'score' => 3.00, 'teacherSay' => 'test say'),
        );

        $this->getDao()->updateItemEssays(1, $testRes);

        $results = $this->getDao()->findItemResultsByResultId(1, 'testpaper');
        $first = reset($results);
        $this->assertEquals(3.00, $first['score']);

        $testRes = array();
        $return = $this->getDao()->updateItemEssays(1, $testRes);
        $this->assertEquals(array(), $return);
    }

    /**
     * @throws \Exception
     *                    功能驴唇不对马嘴
     */
    public function testFindTestResultsByItemIdAndTestId()
    {
        $answers = array(
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array(),
        );
        $this->getDao()->addItemAnswers(1, $answers, 1, 1);

        $results = $this->getDao()->findTestResultsByItemIdAndTestId(array(1, 2, 3, 4), 1);
        $this->assertEquals(4, count($results));
        $results = $this->getDao()->findTestResultsByItemIdAndTestId(array(), 1);
        $this->assertEquals(0, count($results));
    }

    public function testCountRightItemByTestPaperResultId()
    {
        $this->mockDataObject(array('status' => 'right'));

        $resultsCount = $this->getDao()->countRightItemByTestPaperResultId(1);
        $this->assertEquals(1, $resultsCount);
    }

    public function testFindWrongResultByUserId()
    {
        $this->mockDataObject(array('status' => 'wrong'));

        $results = $this->getDao()->findWrongResultByUserId(1, 0, 100);
        $this->assertEquals(1, count($results));
    }

    public function testCountWrongResultByUserId()
    {
        $this->mockDataObject(array('status' => 'wrong'));

        $resultsCount = $this->getDao()->countWrongResultByUserId(1);
        $this->assertEquals(1, $resultsCount);
    }

    public function testDeleteTestpaperItemResultByTestpaperId()
    {
        $this->mockDataObject(array('status' => 'wrong'));
        $resultsCount = $this->getDao()->deleteTestpaperItemResultByTestpaperId(1);
        $this->assertEquals(1, $resultsCount);
    }

    public function getDefaultMockFields()
    {
        return array(
            'itemId' => 0,
            'testId' => 1,
            'resultId' => 1,
            'userId' => 1,
            'questionId' => 1,
            'status' => 'noAnswer',
            'score' => 2.00,
            'answer' => array(),
            'type' => 'testpaper',
        );
    }
}
