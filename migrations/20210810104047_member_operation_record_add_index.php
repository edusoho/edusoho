<?php

use Phpmig\Migration\Migration;

class MemberOperationRecordAddIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `member_operation_record` ADD INDEX `userId` (`user_id`);');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `member_operation_record` DROP INDEX `userId`;');
    }
}
