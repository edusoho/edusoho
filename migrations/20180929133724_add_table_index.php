<?php

use Phpmig\Migration\Migration;

class AddTableIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->createIndex('course_member', 'userid', 'userId');

        $this->createIndex('course_member', 'role_classroom_createdTime', 'role, classroomId, createdTime');

        $this->createIndex('course_task_result', 'finishedTime', 'finishedTime');

        $this->createIndex('member_operation_record', 'operateType_operateTime', 'operate_type, operate_time');

        $this->createIndex('xapi_activity_watch_log', 'userId_activityId', 'user_id, activity_id');

        $this->createIndex('member_operation_record', 'operateType_targetType', 'operate_type, target_type');

        $this->createIndex('member_operation_record', 'operate_time', 'operate_time');

        $this->createIndex('status', 'classroomId_createdTime', 'classroomId, createdTime');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->dropIndex('course_member', 'userid');

        $this->dropIndex('course_member', 'role_classroom_createdTime');

        $this->dropIndex('course_task_result', 'finishedTime');

        $this->dropIndex('member_operation_record', 'operateType_operateTime');

        $this->dropIndex('xapi_activity_watch_log', 'userId_activityId');

        $this->dropIndex('member_operation_record', 'operateType_targetType');

        $this->dropIndex('member_operation_record', 'operate_time');

        $this->dropIndex('status', 'classroomId_createdTime');
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

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column});");
        }
    }

    protected function dropIndex($table, $index)
    {
        if ($this->isIndexExist($table, $index)) {
            $this->getConnection()->exec("DROP INDEX {$index} ON {$table};");
        }
    }

    protected function getBiz()
    {
        return $biz = $this->getContainer();
    }
}
