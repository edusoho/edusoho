<?php

namespace Tests\Item\Wrapper;

use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Wrapper\ExportItemsWrapper;
use Tests\IntegrationTestCase;

class ExportItemsWrapperTest extends IntegrationTestCase
{
    public function testWrap()
    {
        $this->initData();
        $exportItemWrapper = new ExportItemsWrapper($this->biz);
        $items = $exportItemWrapper->wrap($this->getItemDao()->findByIds([1, 3, 4, 5, 6, 7, 8]));
        $num = 0;
        foreach ($items as $item) {
            $num++;
            $this->assertEquals("{$num}、", $item['num']);
            $this->assertTrue(in_array($item['difficulty'], ['简单', '一般', '困难']));
            if ('fill' == $item['type']) {
                $this->assertEquals(['李白', '谪仙人|青莲居士'], $item['answer']);
            }
            if ('determine' == $item['type']) {
                $this->assertEquals('正确', $item['answer']);
            }
            if (in_array($item['type'], ['choice', 'uncertain_choice'])) {
                $this->assertEquals('BD', $item['answer']);
            }
        }
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
