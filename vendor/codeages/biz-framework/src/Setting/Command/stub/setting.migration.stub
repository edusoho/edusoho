<?php
use Phpmig\Migration\Migration;

class BizSetting extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_setting` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(64) NOT NULL,
              `data` longtext NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`)
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
        $connection->exec("DROP TABLE `biz_setting`");
    }
}
