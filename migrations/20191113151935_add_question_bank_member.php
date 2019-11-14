<?php

use Phpmig\Migration\Migration;

class AddQuestionBankMember extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `question_bank_member` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `bankId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题库id',
              `userId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题库教师表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('
            DROP TABLE IF EXISTS `question_bank_member`;
        ');
    }
}
