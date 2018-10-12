<?php

namespace Tests\Unit\User\Job;

use Biz\BaseTestCase;
use Biz\TableIndex\Job\AddTableIndexJob;

class AddTableIndexJobTest extends BaseTestCase
{
    public function testClassroomIdIndexExecute()
    {
        if ($this->isIndexExist('course_member', 'userid', 'userId')) {
            $this->getConnection()->exec('DROP INDEX userid ON course_member');
        }
        $this->assertFalse($this->isIndexExist('course_member', 'userid', 'userId'));
        $job = new AddTableIndexJob(array(), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);
        $this->assertTrue($this->isIndexExist('course_member', 'userid', 'userId'));
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
