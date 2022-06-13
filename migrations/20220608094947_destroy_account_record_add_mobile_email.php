<?php

use Phpmig\Migration\Migration;

class DestroyAccountRecordAddMobileEmail extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `destroy_account_record` ADD COLUMN `mobile` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '用户手机号' AFTER `nickname`;
            ALTER TABLE `destroy_account_record` ADD COLUMN `email` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '用户邮箱' AFTER `mobile`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `destroy_account_record` DROP COLUMN `mobile`;
            ALTER TABLE `destroy_account_record` DROP COLUMN `email`;
        ");
    }
}
