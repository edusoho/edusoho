<?php

use Phpmig\Migration\Migration;

class Index extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `notification` ADD INDEX `userid_type` (`userId`, `type`);

            ALTER TABLE `status` ADD INDEX `userid_type_object` (`userId`, `type`(8), `objectType`(6), `objectId`);

            ALTER TABLE `course_note` ADD INDEX `coursesetid_status` (`courseSetId`, `status`);

            ALTER TABLE `orders` ADD INDEX `target_status` (`targetType`(6), `targetId`, `status`);

            ALTER TABLE `user` ADD INDEX `promoted` (`promoted`);

            ALTER TABLE `file` ADD INDEX `uri` (`uri`);

            ALTER TABLE `course_task_result` ADD INDEX `taskid_userid` (`userId`, `courseTaskId`);

            ALTER TABLE `groups_thread_post` ADD INDEX `threadid_postid_createdtime` (`threadId`, `postId`, `createdTime`);

            ALTER TABLE `course_member` ADD INDEX `index_role_userId` (`role`, `userId`);
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
