<?php

use Phpmig\Migration\Migration;

class UserAddMatomoVisitId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("ALTER TABLE `user` ADD COLUMN `matomoVisitId` varchar(64) NOT NULL DEFAULT '';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('ALTER TABLE `user` DROP `matomoVisitId`;');
    }

    public function getConnection()
    {
        $biz = $this->getContainer();

        return $biz['db'];
    }
}
