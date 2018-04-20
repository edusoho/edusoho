<?php

use Phpmig\Migration\Migration;

class AddNeededIndexes extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isIndexExist('activity_learn_log', 'activityid_userid_event')) {
            $connection->exec('
                ALTER TABLE `activity_learn_log` ADD INDEX `activityid_userid_event` (`activityId`, `userId`, `event`(8));
            ');
        }

        if (!$this->isIndexExist('biz_pay_trade', 'type')) {
            $connection->exec('
                ALTER TABLE `biz_pay_trade` ADD INDEX `type`(`type`);
            ');
        }

        if (!$this->isIndexExist('classroom_member', 'classroomId')) {
            $connection->exec('
                ALTER TABLE `classroom_member` ADD INDEX `classroomId`(`classroomId`);
            ');
        }

        if (!$this->isIndexExist('coupon', 'code')) {
            $connection->exec('
                ALTER TABLE `coupon` ADD INDEX `code` (`code`);
            ');
        }

        if (!$this->isIndexExist('course_member', 'courseSetId')) {
            $connection->exec('
                ALTER TABLE `course_member` ADD INDEX `courseSetId`(`courseSetId`);
            ');
        }

        if (!$this->isIndexExist('course_task', 'courseId')) {
            $connection->exec('
                ALTER TABLE `course_task` ADD INDEX `courseId`(`courseId`);
            ');
        }

        if (!$this->isIndexExist('course_task_result', 'courseTaskId_activityId')) {
            $connection->exec('
                ALTER TABLE `course_task_result` ADD INDEX `courseTaskId_activityId`(`courseTaskId`,`activityId`);
            ');
        }

        if (!$this->isIndexExist('course_v8', 'courseSetId_status')) {
            $connection->exec('
                ALTER TABLE `course_v8` ADD INDEX `courseSetId_status`(`courseSetId`,`status`);
            ');
        }

        if (!$this->isIndexExist('course_v8', 'courseSetId')) {
            $connection->exec('
                ALTER TABLE `course_v8` ADD INDEX `courseSetId` (`courseSetId`);
            ');
        }

        if (!$this->isIndexExist('member_operation_record', 'operate_type')) {
            $connection->exec('
                ALTER TABLE `member_operation_record` ADD INDEX `operate_type` (`operate_type`);
            ');
        }

        if (!$this->isIndexExist('member_operation_record', 'order_id')) {
            $connection->exec('
                ALTER TABLE `member_operation_record` add index `order_id` (`order_id`);
            ');
        }

        if (!$this->isIndexExist('status', 'courseId_createdTime')) {
            $connection->exec('
                ALTER TABLE `status` ADD INDEX courseId_createdTime (`courseId`, `createdTime`);
            ');
        }

        if (!$this->isIndexExist('testpaper_item_result_v8', 'resultId_type')) {
            $connection->exec('
                ALTER TABLE `testpaper_item_result_v8` ADD INDEX `resultId_type`(`resultId`,`type`);
            ');
        }

        if (!$this->isIndexExist('testpaper_item_result_v8', 'testId_type')) {
            $connection->exec('
                ALTER TABLE `testpaper_item_result_v8` ADD INDEX `testId_type`(`testId`, `type`);
            ');
        }

        if (!$this->isIndexExist('testpaper_item_v8', 'testId')) {
            $connection->exec('
                ALTER TABLE `testpaper_item_v8` ADD INDEX `testId`(`testId`); 
            ');
        }

        if (!$this->isIndexExist('testpaper_result_v8', 'testId')) {
            $connection->exec('
                ALTER TABLE `testpaper_result_v8` ADD INDEX `testId`(`testId`);
            ');
        }

        if (!$this->isIndexExist('testpaper_v8', 'courseSetId')) {
            $connection->exec('
                ALTER TABLE `testpaper_v8` ADD INDEX `courseSetId`(`courseSetId`);
            ');
        }

        if (!$this->isIndexExist('upload_files_share', 'sourceUserId')) {
            $connection->exec('
                ALTER TABLE `upload_files_share` ADD INDEX `sourceUserId`(`sourceUserId`);
            ');
        }

        if (!$this->isIndexExist('upload_files_share', 'targetUserId')) {
            $connection->exec('
                ALTER TABLE `upload_files_share` ADD INDEX `targetUserId`(`targetUserId`);
            ');
        }

        if (!$this->isIndexExist('upload_files_share', 'createdTime')) {
            $connection->exec('
                ALTER TABLE `upload_files_share` ADD INDEX `createdTime`(`createdTime`);
            ');
        }
    }

    protected function isIndexExist($table, $indexName)
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
