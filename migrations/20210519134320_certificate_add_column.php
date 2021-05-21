<?php

use Phpmig\Migration\Migration;

class CertificateAddColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `certificate` ADD COLUMN `targetStatus` varchar(64) DEFAULT 'published' COMMENT '证书源状态' AFTER targetId;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `certificate` DROP COLUMN `targetStatus`;');
    }
}
