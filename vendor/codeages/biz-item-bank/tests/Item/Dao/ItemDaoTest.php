<?php

namespace Tests\Item\Dao;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Tests\IntegrationTestCase;

class ItemDaoTest extends IntegrationTestCase
{
    public function testFindByIds()
    {
        $this->initData();

        $items = $this->getItemDao()->findByIds([1, 2, 3, 4]);
        $this->assertEquals(4, count($items));
    }

    public function testFindByCategoryIds()
    {
        $this->initData();

        $items = $this->getItemDao()->findByCategoryIds([1, 2]);
        $this->assertEquals(3, count($items));
    }

    public function testGetItemCountGroupByTypes()
    {
        $this->initData();

        $itemCountGroupByTypes = $this->getItemDao()->getItemCountGroupByTypes(['bank_id' => 1]);
        $itemCountGroupByTypes = ArrayToolkit::index($itemCountGroupByTypes, 'type');
        $this->assertEquals(1, $itemCountGroupByTypes['fill']['itemNum']);
        $this->assertEquals(1, $itemCountGroupByTypes['essay']['itemNum']);
        $this->assertEquals(1, $itemCountGroupByTypes['determine']['itemNum']);
        $this->assertEquals(2, $itemCountGroupByTypes['choice']['itemNum']);
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return $this->biz->dao('ItemBank:Item:ItemDao');
    }
}
