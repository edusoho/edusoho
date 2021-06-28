<?php

use Phpmig\Migration\Migration;

class AddAssistantQrcode extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `user` ADD COLUMN `weChatQrCode` varchar(255) NOT NULL DEFAULT '' COMMENT '助教微信二维码' AFTER `largeAvatar`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `user` DROP COLUMN `weChatQrCode`;
        ');
    }
}
