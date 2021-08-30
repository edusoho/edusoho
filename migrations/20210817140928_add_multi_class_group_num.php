<?php

use Phpmig\Migration\Migration;

class AddMultiClassGroupNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `multi_class` ADD COLUMN `service_group_num` int(10) unsigned not null default 0 COMMENT '助教服务组数上限' after `service_num`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `multi_class` DROP COLUMN `service_group_num`;
        ');
    }
}
