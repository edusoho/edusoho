<?php

use Phpmig\Migration\Migration;

class CreateUserFootprint extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE IF NOT EXISTS `user_footprint`(
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `userId` INT(10) UNSIGNED NOT NULL COMMENT '用户id',
                `targetType` VARCHAR(32) NOT NULL COMMENT '目标类型(task)',
                `targetId` INT(10) UNSIGNED NOT NULL COMMENT '目标id(taskId)',
                `event` VARCHAR(32) NOT NULL COMMENT '事件类型(learn)',
                `date` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录时间',
                `createdTime` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
                `updatedTime` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
                PRIMARY KEY(`id`),
                KEY `index_user_date`(`userId`, `date`),
                KEY `index_target_type_id`(`targetType`, `targetId`),
                KEY `index_date`(`date`)
            )ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT '用户足迹';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('DROP TABLE IF EXISTS `user_footprint`');
    }
}
