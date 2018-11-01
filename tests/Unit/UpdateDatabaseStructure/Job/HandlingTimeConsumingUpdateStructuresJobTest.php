<?php

namespace Tests\Unit\User\Job;

use Biz\BaseTestCase;
use Biz\UpdateDatabaseStructure\Job\HandlingTimeConsumingUpdateStructuresJob;

class HandlingTimeConsumingUpdateStructuresJobTest extends BaseTestCase
{
    protected $data = array(
        array(
            'table' => 'course_member',
            'index' => 'userid',
        ),
        array(
            'table' => 'course_member',
            'index' => 'role_classroom_createdTime',
        ),
        array(
            'table' => 'course_task_result',
            'index' => 'finishedTime',
        ),
        array(
            'table' => 'member_operation_record',
            'index' => 'operateType_operateTime',
        ),
        array(
            'table' => 'xapi_activity_watch_log',
            'index' => 'userId_activityId',
        ),
        array(
            'table' => 'member_operation_record',
            'index' => 'operateType_targetType',
        ),
        array(
            'table' => 'member_operation_record',
            'index' => 'operate_time',
        ),
        array(
            'table' => 'status',
            'index' => 'classroomId_createdTime',
        ),
        array(
            'table' => 'user',
            'index' => 'verifiedMobile',
        ),
    );

    public function testJobExecute()
    {
        foreach ($this->data as $value) {
            if ($this->isIndexExist($value['table'], $value['index'])) {
                $table = $value['table'];
                $index = $value['index'];
                $this->getConnection()->exec("DROP INDEX {$index} ON {$table}");
            }
            $this->assertFalse($this->isIndexExist($value['table'], $value['index']));
        }

        $job = new HandlingTimeConsumingUpdateStructuresJob(array(), $this->biz);
        $job->execute();

        foreach ($this->data as $value) {
            $this->assertTrue($this->isIndexExist($value['table'], $value['index']));
        }
    }

    protected function getConnection()
    {
        $biz = $this->getBiz();

        return $biz['db'];
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
