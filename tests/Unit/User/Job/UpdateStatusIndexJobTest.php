<?php

namespace Tests\Unit\User\Job;

use Biz\BaseTestCase;
use Biz\User\Job\UpdateStatusIndexJob;

class UpdateStatusIndexJobTest extends BaseTestCase
{
    public function testClassroomIdIndexExecute()
    {
        if ($this->isIndexExist('status', 'classroomId', 'classroomId')) {
            $this->getConnection()->exec('DROP INDEX index_name ON talbe_name');
        }
        $this->assertFalse($this->isIndexExist('status', 'classroomId', 'classroomId'));
        $job = new UpdateStatusIndexJob(array(), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);
        $this->assertTrue($this->isIndexExist('status', 'classroomId', 'classroomId'));
    }

    protected function getConnection()
    {
        $biz = $this->getBiz();

        return $biz['db'];
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
