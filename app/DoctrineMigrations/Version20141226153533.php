<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141226153533 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("
            ALTER TABLE `money_card_batch` 
            ADD COLUMN `coin` int NOT NULL DEFAULT 0 AFTER `money`;
        ");
        $this->addSql("
            ALTER TABLE `money_card_batch` 
            ADD COLUMN `batchName` VARCHAR(15) NOT NULL DEFAULT '' AFTER `note`;
        "); 


        $this->addSql("
	        ALTER TABLE `money_card_batch` 
				CHANGE COLUMN `cardLength` `cardLength` INT(8) NOT NULL DEFAULT 0 ,
				CHANGE COLUMN `number` `number` INT(11) NOT NULL DEFAULT 0 ,
				CHANGE COLUMN `rechargedNumber` `rechargedNumber` INT(11) NOT NULL DEFAULT 0 ,
				CHANGE COLUMN `money` `money` INT(8) NOT NULL DEFAULT 0 ,
				CHANGE COLUMN `userId` `userId` INT(11) NOT NULL DEFAULT 0 ,
				CHANGE COLUMN `createdTime` `createdTime` INT(11) NOT NULL DEFAULT 0 ;
		");

		$this->addSql("
			ALTER TABLE `money_card` 
				CHANGE COLUMN `rechargeTime` `rechargeTime` INT(10) NOT NULL DEFAULT 0 COMMENT '充值时间，0为未充值' ,
				CHANGE COLUMN `cardStatus` `cardStatus` ENUM('normal','invalid','recharged') NOT NULL DEFAULT 'invalid' ,
				CHANGE COLUMN `rechargeUserId` `rechargeUserId` INT(11) NOT NULL DEFAULT 0 ,
				CHANGE COLUMN `batchId` `batchId` INT(11) NOT NULL DEFAULT 0 ;
		");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
