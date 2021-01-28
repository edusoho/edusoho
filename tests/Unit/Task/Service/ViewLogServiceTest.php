<?php

namespace Tests\Unit\Task\Service;

use Biz\BaseTestCase;

class ViewLogServiceTest extends BaseTestCase
{
    public function testCreateViewLog()
    {
        $this->mockBiz(
            'Task:ViewLogDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(
                        array('title' => 'title'),
                    ),
                ),
            )
        );

        //　上面的mock方法指定了　参数为一个数组，如果传入的参数和指定的参数不一样，会报错
        $result = $this->getViewLogService()->createViewLog(array('title' => 'title'));

        $this->assertArrayEquals(
            array('id' => 111, 'title' => 'title'),
            $result
        );
    }

    public function testSearchViewLogs()
    {
        $this->mockBiz(
            'Task:ViewLogDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(
                        array('title' => 'title'),
                        array('id', 'asc'),
                        0,
                        5,
                    ),
                ),
            )
        );

        $result = $this->getViewLogService()->searchViewLogs(
            array('title' => 'title'),
            array('id', 'asc'),
            0,
            5
        );
        $this->assertArrayEquals(
            array('id' => 111, 'title' => 'title'),
            $result
        );
    }

    public function testSearchViewLogsGroupByTime()
    {
        $this->mockBiz(
            'Task:ViewLogDao',
            array(
                array(
                    'functionName' => 'searchGroupByTime',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(
                        array('title' => 'title'),
                        12312322211,
                        12312322311,
                    ),
                ),
            )
        );

        $result = $this->getViewLogService()->searchViewLogsGroupByTime(
            array('title' => 'title'),
            12312322211,
            12312322311
        );
        $this->assertArrayEquals(
            array('id' => 111, 'title' => 'title'),
            $result
        );
    }

    public function testCountViewLogs()
    {
        $this->mockBiz(
            'Task:ViewLogDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 123,
                    'withParams' => array(array('title' => 'title')),
                ),
            )
        );

        $result = $this->getViewLogService()->countViewLogs(array('title' => 'title'));
        $this->assertEquals(123, $result);
    }

    protected function getViewLogService()
    {
        return $this->createService('Task:ViewLogService');
    }

    protected function getViewLogDao()
    {
        return $this->createDao('Task:ViewLogDao');
    }
}
