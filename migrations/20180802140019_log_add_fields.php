<?php

use Phpmig\Migration\Migration;

class LogAddFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("
          ALTER TABLE `log` ADD `browser` varchar(120) DEFAULT '' COMMENT '浏览器信息' AFTER `ip`;
          ALTER TABLE `log` ADD `operatingSystem` varchar(120) DEFAULT '' COMMENT '操作系统' AFTER `browser`;
          ALTER TABLE `log` ADD `device` varchar(120) DEFAULT '' COMMENT '移动端或者计算机 移动端mobile 计算机computer' AFTER `operatingSystem`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if ($this->isFieldExist('log', 'browser')) {
            $db->exec('ALTER TABLE `log` DROP `browser`;');
        }

        if ($this->isFieldExist('log', 'operatingSystem')) {
            $db->exec('ALTER TABLE `log` DROP `operatingSystem`;');
        }

        if ($this->isFieldExist('log', 'device')) {
            $db->exec('ALTER TABLE `log` DROP `device`;');
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
