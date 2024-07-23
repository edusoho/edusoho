<?php

use Phpmig\Migration\Migration;

class CreateTableForAiAnswer extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()['db']->exec("
            CREATE TABLE IF NOT EXISTS `ai_answer_result` (
			    `id` INT(10) NOT NULL AUTO_INCREMENT,
                `app` VARCHAR(32) NOT NULL COMMENT 'ai应用',
                `inputsHash` CHAR(32) NOT NULL COMMENT '参数hash',
                `answer` TEXT NOT NULL COMMENT 'ai回答',
                `createdTime` INT(10) NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `app_inputs_hash` (`app`, `inputsHash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

            CREATE TABLE IF NOT EXISTS `ai_answer_record` (
			    `id` INT(10) NOT NULL AUTO_INCREMENT,
                `userId` INT(10) NOT NULL COMMENT '用户id',
                `app` VARCHAR(32) NOT NULL COMMENT 'ai应用',
                `inputsHash` CHAR(32) NOT NULL COMMENT '参数hash',
                `resultId` INT(10) NOT NULL COMMENT 'ai生成结果id',
                `createdTime` INT(10) NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `user_id_app_inputs_hash` (`userId`, `app`, `inputsHash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()['db']->exec('
            DROP TABLE `ai_answer_result`;
            DROP TABLE `ai_answer_record`;
        ');
    }
}
