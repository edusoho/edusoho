<?php

use Phpmig\Migration\Migration;

class ActivityAudioAddIsTextAudio extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("ALTER TABLE `activity_audio` ADD COLUMN `hasText` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否包含图文';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('ALTER TABLE `activity_audio` DROP `hasText`;');
    }

    protected function getBiz()
    {
        return $biz = $this->getContainer();
    }

    protected function getConnection()
    {
        $biz = $this->getBiz();

        return $biz['db'];
    }
}
