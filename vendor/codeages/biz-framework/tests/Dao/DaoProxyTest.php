<?php

namespace Tests\Dao;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use Codeages\Biz\Framework\Dao\ArrayStorage;
use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Dao\FieldSerializer;
use Codeages\Biz\Framework\Dao\DaoProxy;
use Prophecy\Argument;

class DaoProxyTest extends TestCase
{
    public function testGet_HitCache()
    {
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'get');
        $row = $proxy->get($expected['id']);

        $this->assertEquals($expected['id'], $row['id']);
    }

    public function testGet_MissCache()
    {
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'get');
        $row = $proxy->get($expected['id']);

        $this->assertEquals($expected, $row);
    }

    public function testGet_NoCache()
    {
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'get');
        $row = $proxy->get($expected['id']);
        $this->assertEquals($expected, $row);
    }

    public function testGet_MultiCall_HitArrayStorageCache()
    {
        $storage = new ArrayStorage();
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'get', $storage);
        $row = $proxy->get($expected['id']);
        $this->assertEquals($expected, $row);

        $proxy = $this->mockDaoProxyWithNoCacheAndNoRealCall($storage);
        $row = $proxy->get($expected['id']);
        $this->assertEquals($expected, $row);
    }

    public function testGet_Lock()
    {
        $expected = array('id' => 1, 'name' => 'test');

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn(array());
        $dao->get(Argument::cetera())->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = true;

        $proxy = new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer);

        $row = $proxy->get($expected['id'], array('lock' => true));

        $this->assertEquals($expected['id'], $row['id']);
    }

    /**
     * @group current
     */
    public function testFind_HitCache()
    {
        $expected = array(
            array('id' => 1, 'name' => 'test 1'),
            array('id' => 2, 'name' => 'test 2'),
        );
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'find');

        $rows = $proxy->find();

        $this->assertEquals($expected, $rows);
    }

    public function testSearch_HitCache()
    {
        $expected = array(
            array('id' => 1, 'name' => 'test 1'),
            array('id' => 2, 'name' => 'test 2'),
        );
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'search');
        $rows = $proxy->search();

        $this->assertEquals($expected, $rows);
    }

    public function testSearch_MissCache()
    {
        $expected = array(
            array('id' => 1, 'name' => 'test 1'),
            array('id' => 2, 'name' => 'test 2'),
        );
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'search');
        $row = $proxy->search(array(), array(), 0, 100);

        $this->assertEquals($expected, $row);
    }

    public function testSearch_NoCache()
    {
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'search');
        $rows = $proxy->search(array(), array(), 0, 1);
        $this->assertEquals($expected, $rows);
    }

    public function testCount_HitCache()
    {
        $expected = 2;
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'count');
        $count = $proxy->count();

        $this->assertEquals($expected, $count);
    }

    public function testCount_MissCache()
    {
        $expected = 2;
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'count');
        $count = $proxy->count(array());

        $this->assertEquals($expected, $count);
    }

    public function testCount_NoCache()
    {
        $expected = 1;
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'count');
        $count = $proxy->count(array());

        $this->assertEquals($expected, $count);
    }

    private function mockDaoProxyWithHitCache($expected, $proxyMethod, $arrayStorage = null)
    {
        $strategy = $this->prophesize('Codeages\Biz\Framework\Dao\CacheStrategy');
        $strategy->beforeQuery(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::any(),
            Argument::type('array')
        )->willReturn($expected);

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = true;
        $biz['dao.cache.strategy.default'] = $strategy->reveal();

        return new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer, $arrayStorage);
    }

    private function mockDaoProxyWithMissCache($expected, $proxyMethod, $arrayStorage = null)
    {
        $strategy = $this->prophesize('Codeages\Biz\Framework\Dao\CacheStrategy');
        $strategy->beforeQuery(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::type('string'),
            Argument::type('array')
        )->willReturn(false);
        $strategy->afterQuery(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::type('string'),
            Argument::type('array'),
            Argument::any()
        )->willReturn(null);

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn(array());
        $dao->$proxyMethod(Argument::cetera())->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = true;
        $biz['dao.cache.strategy.default'] = $strategy->reveal();

        return new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer, $arrayStorage);
    }

    private function mockDaoProxyWithNoCache($expected, $proxyMethod, $arrayStorage = null)
    {
        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn(array());
        $dao->table()->willReturn('example');
        $dao->$proxyMethod(Argument::cetera())->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = false;

        return new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer, $arrayStorage);
    }

    private function mockDaoProxyWithNoCacheAndNoRealCall($arrayStorage = null)
    {
        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn(array());
        $dao->table()->willReturn('example');

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = false;

        return new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer, $arrayStorage);
    }
}
