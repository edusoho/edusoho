<?php

use Phpmig\Migration\Migration;

class UserProfileAddSelectType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
                            ALTER TABLE `user_profile` ADD COLUMN `selectField1` varchar(64) DEFAULT NULL COMMENT '下拉类型字段';
                            ALTER TABLE `user_profile` ADD COLUMN `selectField2` varchar(64) DEFAULT NULL COMMENT '下拉类型字段';
                            ALTER TABLE `user_profile` ADD COLUMN `selectField3` varchar(64) DEFAULT NULL COMMENT '下拉类型字段';
                            ALTER TABLE `user_profile` ADD COLUMN `selectField4` varchar(64) DEFAULT NULL COMMENT '下拉类型字段';
                            ALTER TABLE `user_profile` ADD COLUMN `selectField5` varchar(64) DEFAULT NULL COMMENT '下拉类型字段';
                            ALTER TABLE `user_field` ADD COLUMN `detail` text;
                            ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
                         ALTER TABLE `user_profile` DROP COLUMN `selectField1`;
                         ALTER TABLE `user_profile` DROP COLUMN `selectField2`;
                         ALTER TABLE `user_profile` DROP COLUMN `selectField3`;
                         ALTER TABLE `user_profile` DROP COLUMN `selectField4`;
                         ALTER TABLE `user_profile` DROP COLUMN `selectField5`;
                        ');
    }
}
