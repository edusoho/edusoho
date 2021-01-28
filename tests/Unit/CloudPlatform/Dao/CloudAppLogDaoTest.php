<?php

namespace Tests\Unit\CloudPlatform\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CloudAppLogDaoTest extends BaseDaoTestCase
{
    public function testGetLastLogByCodeAndToVersion()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('code' => 'MAIN', 'toVersion' => '8.0.0'));
        $res = $this->getDao()->getLastLogByCodeAndToVersion('MAIN', '8.0.0');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
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

    public function testCountLogs()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('code' => 'code1'));
        $expected[] = $this->mockDataObject(array('code' => 'code2'));
        $expected[] = $this->mockDataObject(array('code' => 'code3'));
        $expected[] = $this->mockDataObject(array('code' => 'code4'));
        $expected[] = $this->mockDataObject(array('code' => 'code5'));

        $count = $this->getDao()->countLogs(array());

        $this->assertEquals(5, $count);
    }

    protected function getDefaultMockfields()
    {
        return array(
            'code' => 'testCode',
            'name' => 'test code',
            'fromVersion' => '1.0.0',
            'toVersion' => '2.0.0',
            'type' => 'install',
            'dbBackupPath' => '',
            'sourceBackupPath' => '',
            'status' => 'SUCCESS',
            'userId' => 1,
            'ip' => '127.0.0.1',
            'message' => '',
            'createdTime' => time(),
        );
    }
}
