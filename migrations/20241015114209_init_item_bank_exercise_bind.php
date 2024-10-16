<?php

use Phpmig\Migration\Migration;

class InitItemBankExerciseBind extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("
              CREATE TABLE `item_bank_exercise_bind` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `bindId` int(11) NOT NULL,
              `bindType` varchar(64) NOT NULL COMMENT '绑定类型classroom, course',
              `itemBankExerciseId` int(11) NOT NULL,
              `seq` int(11) NOT NULL COMMENT '顺序',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idx_itemBankExerciseId` (`itemBankExerciseId`),
              KEY `idx_bindType_bindId` (`bindType`, `bindId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE `item_ban_exercise_auto_join_record`  (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(10) NOT NULL,
              `itemBankExerciseId` int(11) NOT NULL,
              `itemBankExerciseBindId` int(11) NOT NULL,
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idx_itemBankExerciseId` (`itemBankExerciseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            ALTER TABLE `item_bank_exercise_member` ADD COLUMN `canLearn` tinyint(1) NOT NULL COMMENT '可以学习' AFTER `deadlineNotified`;
            
            ALTER TABLE `item_bank_exercise` ADD COLUMN `updated_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新人' AFTER `creator`;
            
            UPDATE `item_bank_exercise` SET `updated_user_id` = `creator`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('
          DROP TABLE IF EXISTS `item_bank_exercise_bind`;
          DROP TABLE IF EXISTS `item_ban_exercise_auto_join_record`;
          ALTER TABLE item_bank_exercise_member DROP COLUMN canLearn;
          ALTER TABLE item_bank_exercise DROP COLUMN updated_user_id;
        ');
    }

    private function getConnection()
    {
        return $this->getContainer()->offsetGet('db');
    }
}
