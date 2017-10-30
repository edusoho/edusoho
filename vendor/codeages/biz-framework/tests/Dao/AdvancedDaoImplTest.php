<?php

namespace Tests;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Tests\Example\Dao\AdvancedExampleDao;

class AdvancedDaoImplTest extends IntegrationTestCase
{
    public function testDeleteWithOtherField()
    {
        $dao = $this->getAdvancedExampleDao();

        $dao->create(array(
            'name' => 'test1',
        ));

        $dao->create(array(
            'name' => 'test1',
        ));

        $dao->create(array(
            'name' => 'test1',
        ));

        $deleted = $dao->batchDelete(array('name' => 'test1'));

        $this->assertEquals(3, $deleted);
    }

    public function testDeleteWithIds()
    {
        $dao = $this->getAdvancedExampleDao();

        $row1 = $dao->create(array(
            'name' => 'test1',
        ));

        $row2 = $dao->create(array(
            'name' => 'test1',
        ));

        $row3 = $dao->create(array(
            'name' => 'test1',
        ));

        $row4 = $dao->create(array(
            'name' => 'test1',
        ));

        $deleted = $dao->batchDelete(array('ids' => array($row1['id'], $row2['id'], $row3['id'])));

        $this->assertEquals(3, $deleted);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testDeleteWithEmpty()
    {
        $dao = $this->getAdvancedExampleDao();

        $dao->batchDelete(array('ids' => array()));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testDeleteWithNotInDeclare()
    {
        $dao = $this->getAdvancedExampleDao();
        $dao->batchDelete(array('not-exist-column' => array(1, 2, 3, 4)));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testDeleteWithNoDeclare()
    {
        $dao = $this->getAdvancedExample2Dao();
        $dao->batchDelete(array('not-exist-column' => array(1, 2, 3, 4)));
    }

    public function testDeleteWithCache()
    {
        $this->biz['dao.cache.enabled'] = true;

        $dao = $this->getAdvancedExampleDao();

        $row1 = $dao->create(array(
            'name' => 'test1',
        ));

        $row2 = $dao->create(array(
            'name' => 'test2',
        ));

        $row3 = $dao->create(array(
            'name' => '3test',
        ));

        $row4 = $dao->create(array(
            'name' => '4test1',
        ));

        $this->getAdvancedExampleDao()->get($row1['id']);

        $dao->batchDelete(array('pre_like' => 'test'));

        $newRow1 = $this->getAdvancedExampleDao()->get($row1['id']);

        $this->assertNull($newRow1);
    }

    public function testDeleteWithLikeKey()
    {
        $dao = $this->getAdvancedExampleDao();

        $row1 = $dao->create(array(
            'name' => 'test1',
        ));

        $row2 = $dao->create(array(
            'name' => 'test2',
        ));

        $row3 = $dao->create(array(
            'name' => '3test',
        ));

        $row4 = $dao->create(array(
            'name' => '4test1',
        ));

        $deleted = $dao->batchDelete(array('pre_like' => 'test'));

        $this->assertEquals(2, $deleted);
    }

    public function testBatchCreate()
    {
        $count = 10000;
        $this->createBatchRecord(10000);

        $total = $this->getAdvancedExampleDao()->count(array());

        $this->assertEquals($count, $total);
    }

    public function testBatchUpdate()
    {
        $this->biz['dao.cache.enabled'] = true;
        $count = 1000;

        $this->createBatchRecord($count);

        $examples = $this->getAdvancedExampleDao()->search(array(), array(), 0, $count);

        $batchUpdates = array();
        foreach ($examples as $example) {
            $update['name'] = 'change_name_'.$example['id'];
            $update['content'] = 'change_content_'.$example['id'];
            $update['ids1'] = array(4, 5, 6);
            $update['ids2'] = array(4, 5, 6);
            $batchUpdates[] = $update;
        }

        $beforeUpdateTime = time();

        $firstOneId = $examples[0]['id'];
        $beforeUpdateExample = $this->getAdvancedExampleDao()->get($firstOneId);

        $this->getAdvancedExampleDao()->batchUpdate(ArrayToolkit::column($examples, 'id'), $batchUpdates);

        $examples = $this->getAdvancedExampleDao()->search(array(), array(), 0, $count);

        $this->assertEquals('change_name_'.$examples[0]['id'], $examples[0]['name']);
        $this->assertEquals('change_content_'.$examples[0]['id'], $examples[0]['content']);
        $this->assertEquals('change_name_'.$examples[400]['id'], $examples[400]['name']);
        $this->assertEquals('change_content_'.$examples[400]['id'], $examples[400]['content']);

        $this->assertEquals(array(4, 5, 6), $examples[0]['ids1']);
        $this->assertEquals(array(4, 5, 6), $examples[0]['ids2']);

        $this->assertGreaterThanOrEqual($beforeUpdateTime, $examples[0]['updated_time']);

        $afterUpdateExample = $this->getAdvancedExampleDao()->get($firstOneId);

        $this->assertNotEquals($beforeUpdateExample, $afterUpdateExample);
    }

    private function createBatchRecord($count)
    {
        $news = array();
        for ($i = 1; $i <= $count; ++$i) {
            $fields = array(
                'name' => 'test'.$i,
                'content' => 'content',
                'ids1' => array(1, 2, 3),
                'ids2' => array(1, 2, 3),
            );
            $news[] = $fields;
        }

        $this->getAdvancedExampleDao()->batchCreate($news);
    }

    /**
     * @return AdvancedExampleDao
     */
    private function getAdvancedExampleDao()
    {
        return $this->biz->dao('Example:AdvancedExampleDao');
    }

    /**
     * @return AdvancedExampleDao
     */
    private function getAdvancedExample2Dao()
    {
        return $this->biz->dao('Example:AdvancedExample2Dao');
    }
}
