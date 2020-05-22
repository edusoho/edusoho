<?php

namespace Tests\Item\Dao;

use Codeages\Biz\ItemBank\Item\Dao\ItemCategoryDao;
use Tests\IntegrationTestCase;

class ItemCategoryDaoTest extends IntegrationTestCase
{
    public function testFindByIds()
    {
        $this->initData();

        $categories = $this->getItemCategoryDao()->findByIds([1, 2, 3, 4]);
        $this->assertEquals(4, count($categories));
    }

    public function testFindByBankId()
    {
        $this->initData();

        $categories = $this->getItemCategoryDao()->findByBankId(1);
        $this->assertEquals(5, count($categories));
        $categories = $this->getItemCategoryDao()->findByBankId(2);
        $this->assertEquals(1, count($categories));
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }

    /**
     * @return ItemCategoryDao
     */
    protected function getItemCategoryDao()
    {
        return $this->biz->dao('ItemBank:Item:ItemCategoryDao');
    }
}
