<?php

namespace Tests\Dao;

use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Dao\FieldSerializer;
use Codeages\Biz\Framework\Dao\DaoProxy;
use Prophecy\Argument;

class DaoProxyTest extends TestCase
{
    public function testGetWithHitCache()
    {
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'get');
        $row = $proxy->get($expected['id']);

        $this->assertEquals($expected['id'], $row['id']);
    }

    public function testGetWithMissCache()
    {
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'get');
        $row = $proxy->get($expected['id']);

        $this->assertEquals($expected, $row);
    }

    public function testGetWithNoCache()
    {
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'get');
        $row = $proxy->get($expected['id']);
        $this->assertEquals($expected, $row);
    }

    /**
     * @group current
     *
     * @return [type] [description]
     */
    public function testGetWithLock()
    {
        $expected = array('id' => 1, 'name' => 'test');

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn(array());
        $dao->get(Argument::cetera())->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.first.enabled'] = true;
        $biz['dao.cache.second.enabled'] = true;

        $proxy = new DaoProxy($biz, $dao->reveal(), $serializer);

        $row = $proxy->get($expected['id'], array('lock' => true));

        $this->assertEquals($expected['id'], $row['id']);
    }

    public function testFindWithHitCache()
    {
        $expected = array(
            array('id' => 1, 'name' => 'test 1'),
            array('id' => 2, 'name' => 'test 2'),
        );
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'find');
        $rows = $proxy->find();

        $this->assertEquals($expected, $rows);
    }

    public function testSearchWithHitCache()
    {
        $expected = array(
            array('id' => 1, 'name' => 'test 1'),
            array('id' => 2, 'name' => 'test 2'),
        );
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'search');
        $rows = $proxy->search();

        $this->assertEquals($expected, $rows);
    }

    public function testSearchWithMissCache()
    {
        $expected = array(
            array('id' => 1, 'name' => 'test 1'),
            array('id' => 2, 'name' => 'test 2'),
        );
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'search');
        $row = $proxy->search(array(), array(), 0, 100);

        $this->assertEquals($expected, $row);
    }

    public function testSearchWithNoCache()
    {
        $expected = array('id' => 1, 'name' => 'test');
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'search');
        $rows = $proxy->search(array(), array(), 0, 1);
        $this->assertEquals($expected, $rows);
    }

    public function testCountWithHitCache()
    {
        $expected = 2;
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'count');
        $count = $proxy->count();

        $this->assertEquals($expected, $count);
    }

    public function testCountWithMissCache()
    {
        $expected = 2;
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'count');
        $count = $proxy->count(array());

        $this->assertEquals($expected, $count);
    }

    public function testCountWithNoCache()
    {
        $expected = 1;
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'count');
        $count = $proxy->count(array());

        $this->assertEquals($expected, $count);
    }

    private function mockDaoProxyWithHitCache($expected, $proxyMethod)
    {
        $method = 'before'.ucfirst($proxyMethod);

        $strategy = $this->prophesize('Codeages\Biz\Framework\Dao\CacheStrategy');
        $strategy->$method(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::any(),
            Argument::type('array')
        )->willReturn($expected);

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.first.enabled'] = false;
        $biz['dao.cache.second.enabled'] = true;
        $biz['dao.cache.second.strategy.default'] = $strategy->reveal();

        return new DaoProxy($biz, $dao->reveal(), $serializer);
    }

    private function mockDaoProxyWithMissCache($expected, $proxyMethod)
    {
        $beforeMethod = 'before'.ucfirst($proxyMethod);
        $afterMethod = 'after'.ucfirst($proxyMethod);

        $strategy = $this->prophesize('Codeages\Biz\Framework\Dao\CacheStrategy');
        $strategy->$beforeMethod(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::type('string'),
            Argument::type('array')
        )->willReturn(false);
        $strategy->$afterMethod(
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
        $biz['dao.cache.first.enabled'] = false;
        $biz['dao.cache.second.enabled'] = true;
        $biz['dao.cache.second.strategy.default'] = $strategy->reveal();

        return new DaoProxy($biz, $dao->reveal(), $serializer);
    }

    private function mockDaoProxyWithNoCache($expected, $proxyMethod)
    {
        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn(array());
        $dao->$proxyMethod(Argument::cetera())->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.first.enabled'] = false;
        $biz['dao.cache.second.enabled'] = false;

        return new DaoProxy($biz, $dao->reveal(), $serializer);
    }
}
