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

        $connection->exec('
            ALTER TABLE `activity_learn_log` ADD INDEX `activityid_userid_event` (`activityId`, `userId`, `event`(8));
            ALTER TABLE `biz_pay_trade` ADD INDEX `type`(`type`);
            ALTER TABLE `classroom_member` ADD INDEX `classroomId`(`classroomId`);
            ALTER TABLE `coupon` ADD INDEX `code` (`code`);
            ALTER TABLE `course_member` ADD INDEX `courseSetId`(`courseSetId`);
        ');

        $connection->exec('
            ALTER TABLE `course_task` ADD INDEX `courseId`(`courseId`);
            ALTER TABLE `course_task_result` ADD INDEX `courseTaskId_activityId`(`courseTaskId`,`activityId`);
            ALTER TABLE `course_v8` ADD INDEX `courseSetId_status`(`courseSetId`,`status`);
            ALTER TABLE `course_v8` ADD INDEX `courseSetId` (`courseSetId`);
            ALTER TABLE `member_operation_record` ADD INDEX `operate_type` (`operate_type`);
        ');

        $connection->exec('
            ALTER TABLE `member_operation_record` add index `order_id` (`order_id`);
            ALTER TABLE `status` ADD INDEX courseId_createdTime (`courseId`, `createdTime`);
            ALTER TABLE `testpaper_item_result_v8` ADD INDEX `resultId_type`(`resultId`,`type`);
            ALTER TABLE `testpaper_item_result_v8` ADD INDEX `testId_type`(`testId`, `type`);
            ALTER TABLE `testpaper_item_v8` ADD INDEX `testId`(`testId`); 
        ');

        $connection->exec('
            ALTER TABLE `testpaper_result_v8` ADD INDEX `testId`(`testId`);
            ALTER TABLE `testpaper_v8` ADD INDEX `courseSetId`(`courseSetId`);
            ALTER TABLE `upload_files_share` ADD INDEX `sourceUserId`(`sourceUserId`);
            ALTER TABLE `upload_files_share` ADD INDEX `targetUserId`(`targetUserId`);
            ALTER TABLE `upload_files_share` ADD INDEX `createdTime`(`createdTime`);
        ');

        $connection->exec('
            CREATE UNIQUE INDEX `uuid` ON `user`(`uuid`);
        ');
    }
}
