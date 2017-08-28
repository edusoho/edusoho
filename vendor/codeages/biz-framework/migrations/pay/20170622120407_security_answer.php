<?php

use Phpmig\Migration\Migration;

class SecurityAnswer extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_security_answer` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` INT(10) unsigned NOT NULL COMMENT '所属用户',
              `question_key` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '安全问题的key',
              `answer` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
              `salt` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE (`user_id`, `question_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("drop table `security_answer`");
    }
}
