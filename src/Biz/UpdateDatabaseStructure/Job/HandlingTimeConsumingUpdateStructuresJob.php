<?php

namespace Biz\UpdateDatabaseStructure\Job;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class HandlingTimeConsumingUpdateStructuresJob extends AbstractJob
{
    /*
     * HandlingTimeConsumingUpdateStructuresJob使用范围：
     * 1.因为表过大导致执行时间不可控的加索引sql语句
     * 2.表量级很大，想要添加和业务代码没有强关联的添加字段或者修改字段属性的sql语句，字段的缺失会导致业务报错的语句，严禁在JOB执行
     *
     */
    public function execute()
    {
        $this->addTableIndex();
    }

    protected function addTableIndex()
    {
        /*
         *  Table  biz_answer_question_report
         *  Index  identify
         *  Column identify
         */
        $this->createIndex('biz_answer_question_report', 'identify', 'identify');

        /*
         *  Table  biz_assessment_section_item
         *  Index  seq
         *  Column seq
         */
        $this->createIndex('biz_assessment_section_item', 'seq', 'seq');

        /*
         *  Table  biz_answer_report
         *  Index  answer_scene_id
         *  Column answer_scene_id
         */
        $this->createIndex('biz_answer_report', 'answer_scene_id', 'answer_scene_id');

        /*
         *  Table  biz_pay_trade
         *  Index  user_id
         *  Column user_id
         */
        $this->createIndex('biz_pay_trade', 'user_id', 'user_id');

        /*
         *  Table  course_member
         *  Index  userid
         *  Column userId
         */
        $this->createIndex('course_member', 'userid', 'userId');

        /*
         *  Table  course_member
         *  Index  role_classroom_createdTime
         *  Column role, classroomId, createdTime
         */
        $this->createIndex('course_member', 'role_classroom_createdTime', 'role, classroomId, createdTime');

        /*
         *  Table  course_task_result
         *  Index  finishedTime
         *  Column finishedTime
         */
        $this->createIndex('course_task_result', 'finishedTime', 'finishedTime');

        /*
         *  Table  member_operation_record
         *  Index  operateType_operateTime
         *  Column operate_type, operate_time
         */
        $this->createIndex('member_operation_record', 'operateType_operateTime', 'operate_type, operate_time');

        /*
         *  Table  xapi_activity_watch_log
         *  Index  userId_activityId
         *  Column user_id, activity_id
         */
        $this->createIndex('xapi_activity_watch_log', 'userId_activityId', 'user_id, activity_id');

        /*
         *  Table  member_operation_record
         *  Index  operateType_targetType
         *  Column operate_type, target_type
         */
        $this->createIndex('member_operation_record', 'operateType_targetType', 'operate_type, target_type');

        /*
         *  Table  member_operation_record
         *  Index  operate_time
         *  Column operate_time
         */
        $this->createIndex('member_operation_record', 'operate_time', 'operate_time');

        /*
         *  Table  status
         *  Index  classroomId_createdTime
         *  Column classroomId, createdTime
         */
        $this->createIndex('status', 'classroomId_createdTime', 'classroomId, createdTime');

        /*
         *  Table  user
         *  Index  verifiedMobile
         *  Column verifiedMobile
         */
        $this->createIndex('user', 'verifiedMobile', 'verifiedMobile');

        /*
         *  Table  course_task_result
         *  UniqueIndex  courseTaskId_userId
         *  Column courseTaskId, userId
         */
        $this->createUniqueIndex('course_task_result', 'courseTaskId_userId', 'courseTaskId, userId');

        /*
         *  Table  question
         *  Index  courseSetId
         *  Column courseTaskId
         */
        $this->createIndex('question', 'courseSetId', 'courseSetId');

        /*
         *  Table  question
         *  Index  bankId_categoryId
         *  Column bankId, categoryId
         */
        $this->createIndex('question', 'bankId_categoryId', 'bankId, categoryId');

        /*
         *  Table  course_task_result
         *  Index  courseId
         *  Column courseId
         */
        $this->createIndex('course_task_result', 'courseId', 'courseId');

        /*
         *  Table  status
         *  Index  userid_type_object
         *  Column userId, type, objectType, objectId
         */
        $this->createIndex('status', 'userid_type_object', 'userId, type, objectType, objectId');

        /*
         *  Table  xapi_activity_watch_log
         *  Index  is_push
         *  Column is_push
         */
        $this->createIndex('xapi_activity_watch_log', 'is_push', 'is_push');

        /*
         *  Table  log_v8
         *  Index  module_action_createdTime
         *  Column module, action, createdTime
         */
        $this->createIndex('log_v8', 'module_action_createdTime', 'module, action, createdTime');

        /*
         *  Table  biz_assessment_section_item
         *  Index  assessmentId_seq
         *  Column assessment_id, seq
         */
        $this->createIndex('biz_assessment_section_item', 'assessmentId_seq', 'assessment_id, seq');
    }

    protected function changeTableFiledType()
    {
        /*
         *  Table  course_set_v8
         *  Field  summary
         *  FieldType longtext
         */
        $this->changeFiledType('course_set_v8', 'summary', 'longtext');
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
