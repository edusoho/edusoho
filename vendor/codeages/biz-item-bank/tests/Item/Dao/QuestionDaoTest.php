<?php

namespace Tests\Item\Dao;

use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Tests\IntegrationTestCase;

class QuestionDaoTest extends IntegrationTestCase
{
    public function testFindByItemId()
    {
        $this->initData();

        $questions = $this->getQuestionDao()->findByItemId(3);
        $this->assertEquals(1, count($questions));
    }

    public function testFindByItemsIds()
    {
        $this->initData();

        $questions = $this->getQuestionDao()->findByItemsIds([3, 5]);
        $this->assertEquals(2, count($questions));
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }
}
