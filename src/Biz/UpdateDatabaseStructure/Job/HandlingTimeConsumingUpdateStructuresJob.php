<?php

namespace Biz\UpdateDatabaseStructure\Job;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class HandlingTimeConsumingUpdateStructuresJob extends AbstractJob
{
    protected $indexArr = [
        ['type' => 'add', 'table' => 'xapi_statement', 'key' => 'status_createdTime', 'value' => '`status`,`created_time`'],
        ['type' => 'add', 'table' => 'xapi_statement', 'key' => 'status_pushTime', 'value' => '`status`,`push_time`'],
        ['type' => 'add', 'table' => 'log_v8', 'key' => 'module_action_createdTime', 'value' => '`module`,`action`,`createdTime`'],
        ['type' => 'add', 'table' => 'xapi_activity_watch_log', 'key' => 'is_push', 'value' => '`is_push`'],
        ['type' => 'add', 'table' => 'xapi_activity_watch_log', 'key' => 'userId_activityId', 'value' => '`userId_activityId`'],
        ['type' => 'add', 'table' => 'course_task_result', 'key' => 'courseId', 'value' => '`courseId`'],
        ['type' => 'add', 'table' => 'course_task_result', 'key' => 'courseTaskId_userId', 'value' => '`courseTaskId`,`userId`'],
        ['type' => 'add', 'table' => 'course_task_result', 'key' => 'courseId_status', 'value' => '`courseId`,`status`'],
        ['type' => 'add', 'table' => 'user', 'key' => 'verifiedMobile', 'value' => '`verifiedMobile`'],
        ['type' => 'add', 'table' => 'course_task_result', 'key' => 'finishedTime', 'value' => '`finishedTime`'],
        ['type' => 'add', 'table' => 'course_member', 'key' => 'role_classroom_createdTime', 'value' => '`role`,`classroomId`,`createdTime`'],
        ['type' => 'add', 'table' => 'course_member', 'key' => 'userid', 'value' => '`userId`'],
        ['type' => 'add', 'table' => 'biz_assessment_section_item', 'key' => 'assessmentId_seq', 'value' => '`assessment_id`,`seq`'],
        ['type' => 'add', 'table' => 'biz_pay_trade', 'key' => 'user_id', 'value' => '`user_id`'],
        ['type' => 'add', 'table' => 'biz_pay_trade', 'key' => 'order_sn', 'value' => '`order_sn`'],
        ['type' => 'add', 'table' => 'biz_answer_report', 'key' => 'answer_scene_id', 'value' => '`answer_scene_id`'],
        ['type' => 'add', 'table' => 'biz_assessment_section_item', 'key' => 'seq', 'value' => '`seq`'],
        ['type' => 'add', 'table' => 'biz_answer_question_report', 'key' => 'identify', 'value' => '`identify`'],
        ['type' => 'add', 'table' => 'biz_wrong_question_book_pool', 'key' => 'user_id', 'value' => '`user_id`'],
        ['type' => 'add', 'table' => 'biz_wrong_question_collect', 'key' => 'item_id', 'value' => '`item_id`'],
        ['type' => 'add', 'table' => 'biz_wrong_question_collect', 'key' => 'poolId_itemId', 'value' => '`pool_id`,`item_id`'],
        ['type' => 'add', 'table' => 'biz_wrong_question', 'key' => 'answer_scene_id', 'value' => '`answer_scene_id`'],
        ['type' => 'add', 'table' => 'biz_wrong_question', 'key' => 'item_id', 'value' => '`item_id`'],
        ['type' => 'add', 'table' => 'biz_wrong_question', 'key' => 'user_id', 'value' => '`user_id`'],
        ['type' => 'add', 'table' => 'biz_wrong_question', 'key' => 'collect_id', 'value' => '`collect_id`'],
        ['type' => 'add', 'table' => 'course_material_v8', 'key' => 'lessonId_type', 'value' => '`lessonId`,`type`'],
        ['type' => 'add', 'table' => 'question_analysis', 'key' => 'targetId_targetType_activityId', 'value' => '`targetId`, `targetType`, `activityId`'],
        ['type' => 'add', 'table' => 'notification', 'key' => 'userId', 'value' => '`userId`'],
        ['type' => 'add', 'table' => 'reward_point_account_flow', 'key' => 'userId_targetId_targetType', 'value' => '`userId`, `targetId`, `targetType`'],
        ['type' => 'add', 'table' => 'member_operation_record', 'key' => 'operate_time', 'value' => '`operate_time`'],
        ['type' => 'add', 'table' => 'member_operation_record', 'key' => 'userId', 'value' => '`user_id`'],
        ['type' => 'del', 'table' => 'member_operation_record', 'key' => 'operate_type'],
        ['type' => 'del', 'table' => 'member_operation_record', 'key' => 'operateType_targetType'],
        ['type' => 'del', 'table' => 'member_operation_record', 'key' => 'operateType_operateTime'],
        ['type' => 'add', 'table' => 'member_operation_record', 'key' => 'operateType_targetType_targetId', 'value' => '`operate_type`,`target_type`,`target_id`'],
        ['type' => 'add', 'table' => 'activity_video_watch_record', 'key' => 'startTime_endTime', 'value' => '`startTime`,`endTime`'],
        ['type' => 'add', 'table' => 'user_activity_learn_flow', 'key' => 'userId_sign', 'value' => '`userId`, `sign`'],
        ['type' => 'add', 'table' => 'status', 'key' => 'classroomId_createdTime', 'value' => '`classroomId`,`createdTime`'],
        ['type' => 'del', 'table' => 'status', 'key' => 'userId'],
        ['type' => 'add', 'table' => 'status', 'key' => 'userid_type_object', 'value' => '`userId`,`type`,`objectType`,`objectId`'],
        ['type' => 'add', 'table' => 'activity', 'key' => 'mediaType', 'value' => '`mediaType`'],
        ['type' => 'del', 'table' => 'user_activity_learn_flow', 'key' => 'userId'],
    ];

    /*
     * HandlingTimeConsumingUpdateStructuresJob使用范围：
     * 1.因为表过大导致执行时间不可控的加索引sql语句
     * 2.表量级很大，想要添加和业务代码没有强关联的添加字段或者修改字段属性的sql语句，字段的缺失会导致业务报错的语句，严禁在JOB执行
     * 3.需要在 AddTableIndexV2.php 中同步
     */
    public function execute()
    {
        foreach ($this->indexArr as $index) {
            if ('add' == $index['type']) {
                $this->createIndex($index['table'], $index['key'], $index['value']);
            } else {
                $this->dropIndex($index['table'], $index['key']);
            }
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

    protected function createIndex($table, $index, $column)
    {
        try {
            if (!$this->isIndexExist($table, $index)) {
                $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column});");
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('job', 'create_index', '索引创建失败:'.$e->getMessage());
        }
    }

    protected function dropIndex($table, $index)
    {
        try {
            if ($this->isIndexExist($table, $index)) {
                $this->getConnection()->exec("ALTER TABLE {$table} drop INDEX {$index};");
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('job', 'create_index', '索引删除失败:'.$e->getMessage());
        }
    }

    protected function createField($table, $fieldName, $sql)
    {
        try {
            if (!$this->isFieldExist($table, $fieldName)) {
                $this->getConnection()->exec($sql);
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('job', 'create_field', '字段创建失败:'.$e->getMessage());
        }
    }

    protected function isFieldExist($table, $fieldName)
    {
        $sql = "DESCRIBE `{$table}` `{$fieldName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function createUniqueIndex($table, $index, $column)
    {
        try {
            if (!$this->isIndexExist($table, $index)) {
                $this->getConnection()->exec("ALTER TABLE {$table} ADD UNIQUE INDEX {$index} ({$column});");
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('job', 'create_unique_index', '索引创建失败:'.$e->getMessage());
        }
    }

    protected function changeFiledType($table, $fieldName, $fieldType, $length = '')
    {
        try {
            if ($this->shouldFiledTypeChanged($table, $fieldName, $fieldType)) {
                $this->getConnection()->exec("ALTER TABLE {$table} MODIFY COLUMN {$fieldName} {$fieldType}{$length};");
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('job', 'change_field_type', '类型修改失败:'.$e->getMessage());
        }
    }

    protected function shouldFiledTypeChanged($table, $fieldName, $fieldType)
    {
        $sql = "show columns from `{$table}` where Field = '{$fieldName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        $shouldFiledTypeChanged = false;

        if (!empty($result) && array_key_exists('Type', $result)) {
            if ($result['Type'] != $fieldType) {
                $shouldFiledTypeChanged = true;
            }
        }

        return $shouldFiledTypeChanged;
    }

    protected function getBiz()
    {
        return $this->biz;
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
