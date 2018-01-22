<?php

use Phpmig\Migration\Migration;

class AddMoneyCard extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $sql = "
            CREATE TABLE IF NOT EXISTS `money_card` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cardId` varchar(32) NOT NULL,
            `password` varchar(32) NOT NULL,
            `deadline` varchar(19) NOT NULL COMMENT '有效时间',
            `rechargeTime` INT(10) NOT NULL DEFAULT 0 COMMENT '充值时间，0为未充值',
            `cardStatus` ENUM('normal','invalid','recharged','receive') NOT NULL DEFAULT 'invalid',
            `receiveTime` int(10) NOT NULL DEFAULT '0' COMMENT '领取学习卡时间',
            `rechargeUserId` INT(11) NOT NULL DEFAULT 0,
            `batchId` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `money_card_batch` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cardPrefix` varchar(32) NOT NULL,
            `cardLength` INT(8) NOT NULL DEFAULT 0,
            `number` INT(11) NOT NULL DEFAULT 0,
            `receivedNumber` INT(11) NOT NULL DEFAULT '0',
            `rechargedNumber` INT(11) NOT NULL DEFAULT 0,
            `token` varchar(64) NOT NULL DEFAULT '0',
            `deadline` varchar(19) CHARACTER SET latin1 NOT NULL,
            `money` INT(8) NOT NULL DEFAULT 0,
            `batchStatus` ENUM('invalid','normal') NOT NULL DEFAULT 'normal',
            `coin` int NOT NULL DEFAULT 0,
            `userId` INT(11) NOT NULL DEFAULT 0,
            `createdTime` INT(11) NOT NULL DEFAULT 0,
            `note` varchar(128) NOT NULL,
            `batchName` VARCHAR(15) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        $db->exec($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
