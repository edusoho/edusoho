<?php

namespace Tests\Unit\Xapi\Service;

use Biz\BaseTestCase;
use Biz\Xapi\Service\XapiService;

class XapiServiceTest extends BaseTestCase
{
    public function testUpdateStatementsConvertedAndDataByStatementData()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $params = array(
            $createdStatement['id'] => array(
                'test' => 'test',
            ),
        );

        $this->getXapiService()->updateStatementsConvertedAndDataByStatementData($params);

        $result = $this->getXapiService()->getStatement($createdStatement['id']);
        $this->assertNotEmpty($result['data']);
    }

    public function testUpdateStatusPushedAndPushedTimeByUuids()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);
        $time = time();

        $this->getXapiService()->updateStatusPushedAndPushedTimeByUuids(array($createdStatement['uuid']), $time);
        $result = $this->getXapiService()->getStatement($createdStatement['id']);
        $this->assertEquals('pushed', $result['status']);
        $this->assertEquals($time, $result['push_time']);
    }

    public function testFindWatchLogsByIds()
    {
        $log = array(
            'user_id' => 1,
            'activity_id' => 1,
            'course_id' => 1,
            'task_id' => 1,
            'watched_time' => time(),
        );

        $watchLog = $this->getXapiService()->createWatchLog($log);

        $results = $this->getXapiService()->findWatchLogsByIds(array($watchLog['id']));

        $this->assertContains($watchLog, $results);
    }

    public function testUpdateWatchLog()
    {
        $log = array(
            'user_id' => 1,
            'activity_id' => 1,
            'course_id' => 1,
            'task_id' => 1,
            'watched_time' => time(),
        );

        $watchLog = $this->getXapiService()->createWatchLog($log);

        $result = $this->getXapiService()->getWatchLog($watchLog['id']);

        $this->assertEquals($watchLog, $result);

        $result = $this->getXapiService()->updateWatchLog($watchLog['id'], array('watched_time' => 1000));
        $this->assertEquals(1000, $result['watched_time']);
    }

    public function testGetXapiSdk()
    {
        $sdk = $this->getXapiService()->getXapiSdk();

        $this->assertNotInstanceOf('QiQiuYun\SDK\Service\XAPIService', $sdk);
        $this->assertNull($sdk);
    }

    public function testCreateStatement()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertNotEmpty($result);
        $this->assertEquals($createdStatement['id'], $result['id']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testCreateStatementWithException()
    {
        $this->biz['user'] = null;
        $statement = $this->mockStatement();
        $this->getXapiService()->createStatement($statement);
    }

    public function testBatchCreateStatements()
    {
        $statement = $this->mockStatement();
        $statements = array($statement);
        $this->getXapiService()->batchCreateStatements($statements);

        $result = $this->getXapiService()->searchStatements(array(), array(), 0, 100);

        $this->assertNotEmpty($result);
        $this->assertEquals($statement['user_id'], $result[1]['user_id']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testBatchCreateStatementsWithException()
    {
        $this->biz['user'] = null;
        $statement = $this->mockStatement();
        $statements = array($statement);
        $this->getXapiService()->batchCreateStatements($statements);

        $this->getXapiService()->searchStatements(array(), array(), 0, 100);
    }

    public function testBatchUpdateWatchLogPushed()
    {
        $log = array(
            'user_id' => 1,
            'activity_id' => 1,
            'course_id' => 1,
            'task_id' => 1,
            'watched_time' => time(),
        );

        $watchLog = $this->getXapiService()->createWatchLog($log);
        $this->getXapiService()->batchUpdateWatchLogPushed(array($watchLog['id']));

        $result = $this->getXapiService()->getWatchLog($watchLog['id']);
        $this->assertEquals(1, $result['is_push']);
    }

    public function testDeleteStatement()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertNotEmpty($result);
        $this->assertEquals($createdStatement['id'], $result['id']);

        $this->getXapiService()->deleteStatement($createdStatement['id']);

        $result = $this->getXapiService()->getStatement($createdStatement['id']);
        $this->assertEquals('deleted', $result['status']);
    }

    public function testUpdateStatementsPushedByStatementIds()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $this->getXapiService()->updateStatementsPushedByStatementIds(array($createdStatement['id']));

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertEquals('pushed', $result['status']);
    }

    public function testUpdateStatementsPushingByStatementIds()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $this->getXapiService()->updateStatementsPushingByStatementIds(array($createdStatement['id']));

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertEquals('pushing', $result['status']);
    }

    public function testUpdateStatementsPushedAndDataByStatementData()
    {
        $statement = $this->mockStatement();
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $this->getXapiService()->updateStatementsPushedAndDataByStatementData(array($createdStatement['id'] => array('test' => 'test')));

        $result = $this->getXapiService()->getStatement($createdStatement['id']);

        $this->assertEquals('pushed', $result['status']);
        $this->assertEquals('test', $result['data']['test']);
    }

    public function testSearchStatements()
    {
        $statement = $this->mockStatement();
        $this->getXapiService()->createStatement($statement);

        $results = $this->getXapiService()->searchStatements(array('status' => 'created'), array(), 0, 10);

        $this->assertEquals(2, count($results));
    }

    public function testCountStatements()
    {
        $statement = $this->mockStatement();
        $this->getXapiService()->createStatement($statement);

        $count = $this->getXapiService()->countStatements(array('status' => 'created'));

        $this->assertEquals(2, $count);
    }

    public function testWatchTask()
    {
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'tryTakeTask',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'type' => 'video',
                        'activityId' => 1,
                        'courseId' => 1,
                    ),
                ),
            )
        );
        $this->getXapiService()->watchTask(1, 120);
        $result = $this->getXapiService()->searchWatchLogs(array('taskId' => 1), array(), 0, 10);
        $this->assertCount(1, $result);
    }

    public function testWatchTaskWithTwice()
    {
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'tryTakeTask',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'type' => 'video',
                        'activityId' => 1,
                        'courseId' => 1,
                    ),
                ),
            )
        );
        $this->getXapiService()->watchTask(1, 120);
        $this->getXapiService()->watchTask(1, 120);
        $result = $this->getXapiService()->searchWatchLogs(array('taskId' => 1), array(), 0, 10);
        $this->assertCount(1, $result);
    }

    public function testSearchWatchLogs()
    {
        $log = array(
            'user_id' => 1,
            'activity_id' => 1,
            'course_id' => 1,
            'task_id' => 1,
            'watched_time' => time(),
        );

        $watchLog = $this->getXapiService()->createWatchLog($log);

        $results = $this->getXapiService()->searchWatchLogs(array('ids' => array($watchLog['id'])), array(), 0, 10);
        $this->assertContains($watchLog, $results);
    }

    public function testArchiveStatement()
    {
        $statement = $this->mockStatement();
        $statement['status'] = 'pushed';
        $createdStatement = $this->getXapiService()->createStatement($statement);

        $this->getXapiService()->archiveStatement();

        $result = $this->getXapiService()->getStatement($createdStatement['id']);
        $this->assertEmpty($result);
    }

    private function mockStatement()
    {
        $statement = array(
            'user_id' => 2,
            'verb' => 'watch',
            'target_id' => 1,
            'target_type' => 'video',
            'occur_time' => time(),
        );

        return $statement;
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }
}
