<?php

use Phpmig\Migration\Migration;

class UserAddRegisterVisitId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("ALTER TABLE `user` ADD COLUMN `registerVisitId` varchar(64) NOT NULL DEFAULT '';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('ALTER TABLE `user` DROP `registerVisitId`;');
    }

    public function getConnection()
    {
        $biz = $this->getContainer();

        return $biz['db'];
    }
}
