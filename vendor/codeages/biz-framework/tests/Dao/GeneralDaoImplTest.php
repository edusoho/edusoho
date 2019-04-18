<?php

namespace Tests;

class GeneralDaoImplTest extends IntegrationTestCase
{
    const NOT_EXIST_ID = 9999;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @group current
     */
    public function testGet()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->get($dao);
        }
    }

    public function testUpdateByNameAndCode()
    {
        foreach ($this->getTestDao() as $dao) {
            $dao = $this->biz->dao($dao);
            $row = $dao->create(array(
                'name' => 'test1',
                'code' => 'test1',
            ));

            $affected = $dao->updateByNameAndCode('test1', 'test1', array('content' => 'test'));

            $this->assertEquals(1, $affected);
        }
    }

    private function getTestDao()
    {
        return array(
            'Example:ExampleDao',
            'Example:Example2Dao',
            'Example:Example3Dao',
        );
    }

    private function get($dao)
    {
        $dao = $this->biz->dao($dao);
        $row = $dao->create(array(
            'name' => 'test1',
        ));

        $found = $dao->get($row['id']);
        $this->assertEquals($row['id'], $found['id']);

        $found = $dao->get(self::NOT_EXIST_ID);
        $this->assertEquals(null, $found);
    }

    public function testCreate()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->create($dao);
        }
    }

    private function create($dao)
    {
        $dao = $this->biz->dao($dao);

        $fields = array(
            'name' => 'test1',
            'ids1' => array(1, 2, 3),
            'ids2' => array(1, 2, 3),
        );

        $before = time();

        $saved = $dao->create($fields);

        $this->assertEquals($fields['name'], $saved['name']);
        $this->assertTrue(is_array($saved['ids1']));
        $this->assertCount(3, $saved['ids1']);
        $this->assertTrue(is_array($saved['ids2']));
        $this->assertCount(3, $saved['ids2']);
        $this->assertGreaterThanOrEqual($before, $saved['created_time']);
        $this->assertGreaterThanOrEqual($before, $saved['updated_time']);
    }

    public function testUpdate()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->update($dao);
        }
    }

    private function update($dao)
    {
        $dao = $this->biz->dao($dao);

        $row = $dao->create(array(
            'name' => 'test1',
        ));

        $fields = array(
            'name' => 'test2',
            'ids1' => array(1, 2),
            'ids2' => array(1, 2),
        );

        $before = time();
        $saved = $dao->update($row['id'], $fields);

        $this->assertEquals($fields['name'], $saved['name']);
        $this->assertTrue(is_array($saved['ids1']));
        $this->assertCount(2, $saved['ids1']);
        $this->assertTrue(is_array($saved['ids2']));
        $this->assertCount(2, $saved['ids2']);
        $this->assertGreaterThanOrEqual($before, $saved['updated_time']);
    }

    public function testDelete()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->delete($dao);
        }
    }

    private function delete($dao)
    {
        $dao = $this->biz->dao($dao);

        $row = $dao->create(array(
            'name' => 'test1',
        ));

        $deleted = $dao->delete($row['id']);

        $this->assertEquals(1, $deleted);
    }

    public function testWave()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->wave($dao);
        }
    }

    public function wave($dao)
    {
        $dao = $this->biz->dao($dao);

        $row = $dao->create(array(
            'name' => 'test1',
        ));

        $diff = array('counter1' => 1, 'counter2' => 2);
        $waved = $dao->wave(array($row['id']), $diff);
        $row = $dao->get($row['id']);

        $this->assertEquals(1, $waved);
        $this->assertEquals(1, $row['counter1']);
        $this->assertEquals(2, $row['counter2']);

        $diff = array('counter1' => -1, 'counter2' => -1);
        $waved = $dao->wave(array($row['id']), $diff);
        $row = $dao->get($row['id']);

        $this->assertEquals(1, $waved);
        $this->assertEquals(0, $row['counter1']);
        $this->assertEquals(1, $row['counter2']);
    }

    public function testLikeSearch()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->search($dao);
        }
    }

    private function search($dao)
    {
        $dao = $this->biz->dao($dao);

        $dao->create(array('name' => 'pre_test1'));
        sleep(1);
        $dao->create(array('name' => 'pre_test2'));
        sleep(1);
        $dao->create(array('name' => 'test3_suf'));
        sleep(1);
        $dao->create(array('name' => 'test4_suf'));
        sleep(1);
        $dao->create(array('name' => 'test5'));

        $preNames = $dao->search(array('pre_like' => 'pre_'), array('created_time' => 'desc'), 0, 100);

        $sufNames = $dao->search(array('suf_name' => '_suf'), array('created_time' => 'desc'), 0, 100);

        $likeNames = $dao->search(array('like_name' => 'test'), array('created_time' => 'desc'), 0, 100);

        $this->assertCount(2, $preNames);
        $this->assertCount(2, $sufNames);
        $this->assertCount(5, $likeNames);
        $this->assertEquals('pre_test2', $preNames[0]['name']);
        $this->assertEquals('test3_suf', $sufNames[1]['name']);
        $this->assertEquals('pre_test1', $likeNames[4]['name']);
    }

    public function testInSearch()
    {
        $dao = $this->biz->dao('Example:ExampleDao');

        $tmp1 = $dao->create(array('name' => 'pre_test1'));
        $dao->create(array('name' => 'pre_test2'));
        $tmp2 = $dao->create(array('name' => 'test3_suf'));
        $dao->create(array('name' => 'test4_suf'));

        $results = $dao->search(array('ids' => array($tmp1['id'], $tmp2['id'])), array('created_time' => 'desc'), 0, 100);

        $this->assertCount(2, $results);

        $results = $dao->search(array('ids' => array()), array('created_time' => 'desc'), 0, 100);

        $this->assertCount(4, $results);
    }

    public function testCount()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->daoCount($dao);
        }
    }

    private function daoCount($dao)
    {
        $dao = $this->biz->dao($dao);

        $dao->create(array('name' => 'test1'));
        $dao->create(array('name' => 'test2'));
        $dao->create(array('name' => 'test3'));

        $count = $dao->count(array('name' => 'test2'));

        $this->assertEquals(1, $count);
    }

    public function testFindInFields()
    {
        $dao = $this->biz->dao('Example:ExampleDao');

        $dao->create(array('name' => 'test1', 'ids1' => array('1111'), 'ids2' => array('1111')));
        $dao->create(array('name' => 'test1', 'ids1' => array('1111'), 'ids2' => array('2222')));
        $dao->create(array('name' => 'test2', 'ids1' => array('1111'), 'ids2' => array('3333')));
        $result = $dao->findByNameAndId('test1', '["1111"]');

        $this->assertEquals(count($result), 2);
    }

    public function testTransactional()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->transactional($dao);
        }
    }

    public function transactional($dao)
    {
        $dao = $this->biz->dao($dao);

        $result = $dao->db()->transactional(function () {
            return 1;
        });

        $this->assertEquals(1, $result);
    }

    public function testNullValueUnserializer()
    {
        $dao = $this->biz->dao('Example:ExampleDao');

        $row = $dao->create(array('name' => 'test1'));

        $result = $dao->get($row['id']);
        $this->assertInternalType('array', $result['null_value']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testOrderBysInject()
    {
        /**
         * @var ExampleDao
         */
        $dao = $this->biz->dao('Example:ExampleDao');

        $row = $dao->create(array('name' => 'test1'));

        $dao->findByIds(array(1), array('; SELECT * FROM example'), 0, 10);

        $dao->findByIds(array(1), array('id' => '; SELECT * FROM example'));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testStartInject()
    {
        /**
         * @var ExampleDao
         */
        $dao = $this->biz->dao('Example:ExampleDao');

        $row = $dao->create(array('name' => 'test1'));

        $dao->findByIds(array(1), array('created_time' => 'desc'), '; SELECT * FROM example', 10);
        $dao->findByIds(array(1), array('created_time' => 'desc'), 0, "; UPDATE example SET name = 'inject' WHERE id = 1");
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testLimitInject()
    {
        /**
         * @var ExampleDao
         */
        $dao = $this->biz->dao('Example:ExampleDao');

        $row = $dao->create(array('name' => 'test1'));
        $dao->findByIds(array(1), array('created_time' => 'desc'), 0, "; UPDATE example SET name = 'inject' WHERE id = 1");
    }

    public function testNonInject()
    {
        /**
         * @var ExampleDao
         */
        $dao = $this->biz->dao('Example:ExampleDao');

        $row1 = $dao->create(array('name' => 'testNonInject_1'));

        $result = $dao->findByIds(array($row1['id']), array('created_time' => 'desc'), '0', '2');
        $this->assertCount(1, $result);

        $row2 = $dao->create(array('name' => 'testNonInject_2'));
        $result = $dao->findByIds(array($row1['id'], $row2['id']), array('created_time' => 'desc'), '0', 1);
        $this->assertCount(1, $result);

        $result = $dao->findByIds(array($row1['id'], $row2['id']), array('created_time' => 'desc'), '0', 10);
        $this->assertCount(2, $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testOnlySetStart()
    {
        /**
         * @var ExampleDao
         */
        $dao = $this->biz->dao('Example:ExampleDao');

        $row = $dao->create(array('name' => 'test1'));
        $result = $dao->findByIds(array(1, 2), array('created_time' => 'desc'), '0', null);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testOnlySetLimit()
    {
        /**
         * @var ExampleDao
         */
        $dao = $this->biz->dao('Example:ExampleDao');

        $row = $dao->create(array('name' => 'test1'));
        $result = $dao->findByIds(array(1, 2), array('created_time' => 'desc'), null, 10);
    }

    public function testSerializes()
    {
        /**
         * @var ExampleDao
         */
        $dao = $this->biz->dao('Example:ExampleDao');

        $row = $dao->create(array(
            'name' => 'test1',
            'php_serialize_value' => array('value' => 'i_am_php_serialized_value'),
            'json_serialize_value' => array('value' => 'i_am_json_serialized_value'),
            'delimiter_serialize_value' => array('i_am_delimiter_serialized_value'),
        ));

        foreach (array('php', 'json') as $key) {
            $this->assertEquals($row[$key.'_serialize_value']['value'], "i_am_{$key}_serialized_value");
        }

        $this->assertEquals($row['delimiter_serialize_value'], array('i_am_delimiter_serialized_value'));
    }

    public function testSearchWithColumns()
    {
        /** @var \Tests\Example\Dao\ExampleDao $dao */
        $dao = $this->biz->dao('Example:ExampleDao');

        $dao->create(array('name' => 'test1', 'ids1' => array('1111'), 'ids2' => array('1111')));
        $dao->create(array('name' => 'test1', 'ids1' => array('2222'), 'ids2' => array('2222')));
        $dao->create(array('name' => 'test2', 'ids1' => array('3333'), 'ids2' => array('3333')));

        $results = $dao->search(array('name' => 'test2'), array(), 0, 10, array('name', 'ids1'));
        $this->assertCount(1, $results);
        $this->assertEquals(
            array('name' => 'test2', 'ids1' => array('3333')),
            $results[0]
        );
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testSearchWithErrorColumns()
    {
        /** @var \Tests\Example\Dao\ExampleDao $dao */
        $dao = $this->biz->dao('Example:ExampleDao');

        $dao->search(array('name' => 'test2'), array(), 0, 10, array('* from example;#'));
    }
}
