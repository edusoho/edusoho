<?php

namespace Tests\Unit\Testpaper\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TestpaperItemDaoTest extends BaseDaoTestCase
{
    public function testGetItemsCountByTestId()
    {
        $this->mockDataObject();
        $results = $this->getDao()->getItemsCountByTestId(1);
        $this->assertEquals(1, $results);
    }

    public function testGetItemsCountByParams()
    {
        $this->mockDataObject();
        $result = $this->getDao()->getItemsCountByParams(array('testId' => 1));
        $this->assertEquals(1, $result[0]['num']);
    }

    public function testGetItemsCountByTestIdAndParentId()
    {
        $this->mockDataObject();
        $result = $this->getDao()->getItemsCountByTestIdAndParentId(1, 1);
        $this->assertEquals(1, $result);
    }

    public function testGetItemsCountByTestIdAndQuestionType()
    {
        $this->mockDataObject();
        $result = $this->getDao()->getItemsCountByTestIdAndQuestionType(1, 'choice');
        $this->assertEquals(1, $result);
    }

    public function testFindItemsByIds()
    {
        $this->mockDataObject();

        $result = $this->getDao()->findItemsByIds(array(1, 2));
        $this->assertEquals(1, $result[0]['testId']);
    }

    public function testFindTestpaperItemsByCopyIdAndLockedTestIds()
    {
        $this->mockDataObject();
        $result = $this->getDao()->findTestpaperItemsByCopyIdAndLockedTestIds(1, array(1));
        $this->assertEquals(1, $result[0]['testId']);
    }

    public function testDeleteItemsByParentId()
    {
        $this->mockDataObject();
        $this->getDao()->deleteItemsByParentId(1);
        $result = $this->getDao()->getItemsCountByTestId(1);
        $this->assertEquals(0, $result);
    }

    public function testDeleteItemByIds()
    {
        $this->mockDataObject();
        $this->getDao()->deleteItemByIds(array(1));
        $result = $this->getDao()->getItemsCountByTestId(1);
        $this->assertEquals(0, $result);
    }

    public function testChangeItemsMissScoreByPaperIds()
    {
        $this->mockDataObject();
        $this->getDao()->changeItemsMissScoreByPaperIds(array(1), 4);
        $result = $this->getDao()->get(1);

        $this->assertEquals(1, $result['testId']);
    }

    public function getDefaultMockFields()
    {
        return array(
            'testId' => 1,
            'seq' => 1,
            'questionId' => 1,
            'questionType' => 'choice',
            'parentId' => 1,
            'score' => 2.00,
            'missScore' => 1.00,
            'copyId' => 1,
            'migrateItemId' => 1,
            'type' => 'testpaper',
        );
    }
}
