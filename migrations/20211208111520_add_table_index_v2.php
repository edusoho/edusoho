<?php

use Phpmig\Migration\Migration;

class AddTableIndexV2 extends Migration
{
    protected $biz = null;

    /**
     * 需要在HandlingTimeConsumingUpdateStructuresJob.php 中同步
     * Do the migration
     */
    public function up()
    {
        $this->biz = $this->getContainer();
        $indexArr = [
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
        ['type' => 'add', 'table' => 'biz_order_log', 'key' => 'order_id', 'value' => '`order_id`'],
        ['type' => 'add', 'table' => 'status', 'key' => 'courseId', 'value' => '`courseId`'],
    ];

        foreach ($indexArr as $index) {
            if ('add' == $index['type']) {
                $this->createIndex($index['table'], $index['key'], $index['value']);
            } else {
                $this->dropIndex($index['table'], $index['key']);
            }
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $index)) {
            $this->biz['db']->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column});");
        }
    }

    protected function dropIndex($table, $index)
    {
        if ($this->isIndexExist($table, $index)) {
            $this->biz['db']->exec("ALTER TABLE {$table} drop INDEX {$index};");
        }
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $this->biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
