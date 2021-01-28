<?php

namespace Tests\Unit\CloudPlatform\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CloudAppDaoTest extends BaseDaoTestCase
{
    public function testGetByCode()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array());
        $res = $this->getDao()->getByCode('testCode');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByCodes()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('code' => 'code1'));
        $expected[] = $this->mockDataObject(array('code' => 'code2'));
        $res = $this->getDao()->findByCodes(array('code1', 'code2'));

        $this->assertEquals(2, count($res));
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
        $this->assertArrayEquals($expected[1], $res[1], $this->getCompareKeys());
    }

    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('code' => 'code1'));
        $expected[] = $this->mockDataObject(array('code' => 'code2'));
        $expected[] = $this->mockDataObject(array('code' => 'code3'));
        $expected[] = $this->mockDataObject(array('code' => 'code4'));
        $expected[] = $this->mockDataObject(array('code' => 'code5'));

        $this->searchTestUtil($this->getDao(), array(), $this->getCompareKeys());
    }

    public function testCount()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('code' => 'code1'));
        $expected[] = $this->mockDataObject(array('code' => 'code2'));
        $expected[] = $this->mockDataObject(array('code' => 'code3'));
        $expected[] = $this->mockDataObject(array('code' => 'code4'));
        $expected[] = $this->mockDataObject(array('code' => 'code5'));

        $count = $this->getDao()->count(array());

        $this->assertEquals(5, $count);
    }

    protected function getDefaultMockfields()
    {
        return array(
            'name' => 'test code',
            'code' => 'testCode',
            'type' => 'plugin',
            'protocol' => 3,
            'description' => '',
            'icon' => '',
            'version' => '2.0.0',
            'fromVersion' => '1.0.0',
            'developerId' => 0,
            'developerName' => 'EduSoho官方',
            'installedTime' => time(),
            'updatedTime' => 0,
            'edusohoMinVersion' => '0.0.0',
            'edusohoMaxVersion' => 'up',
        );
    }
}
