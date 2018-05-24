<?php

use Phpmig\Migration\Migration;

class UserAddPasswordInitColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("
          ALTER TABLE  `user` ADD  `passwordInit` TINYINT( 1 ) NOT NULL DEFAULT  '1' COMMENT  '初始化密码' AFTER  `uuid`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('
            ALTER TABLE `user` DROP COLUMN `passwordInit`;
        ');
    }
}
