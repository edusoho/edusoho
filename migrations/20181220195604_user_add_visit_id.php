<?php

use Phpmig\Migration\Migration;

class UserAddVisitId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("ALTER TABLE `user` ADD COLUMN `visitId` varchar(64) NOT NULL DEFAULT '' COMMENT '访客ID';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('ALTER TABLE `user` DROP `visitId`;');
    }

    public function getConnection()
    {
        $biz = $this->getContainer();

        return $biz['db'];
    }
}
