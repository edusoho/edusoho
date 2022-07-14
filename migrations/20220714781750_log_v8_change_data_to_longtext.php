<?php

use Phpmig\Migration\Migration;

class LogV8ChangeDataToLongText extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER  TABLE `log_v8` MODIFY COLUMN `data` longtext COMMENT '日志数据';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}