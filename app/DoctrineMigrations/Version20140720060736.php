<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140720060736 extends AbstractMigration
{
    public function up(Schema $schema)
    {
            $this->addSql(
        	"
        	ALTER TABLE `user_profile` CHANGE `intField1` `intField1` INT(11) UNSIGNED NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `intField2` `intField2` INT(11) UNSIGNED NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `intField3` `intField3` INT(11) UNSIGNED NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `intField4` `intField4` INT(11) UNSIGNED NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `intField5` `intField5` INT(11) UNSIGNED NULL DEFAULT NULL;

        	ALTER TABLE `user_profile` CHANGE `floatField1` `floatField1` FLOAT(10,2)  NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `floatField2` `floatField2` FLOAT(10,2)  NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `floatField3` `floatField3` FLOAT(10,2)  NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `floatField4` `floatField4` FLOAT(10,2)  NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `floatField5` `floatField5` FLOAT(10,2)  NULL DEFAULT NULL;

        	ALTER TABLE `user_profile` CHANGE `dateField1` `dateField1` DATE NULL;
        	ALTER TABLE `user_profile` CHANGE `dateField2` `dateField2` DATE NULL;
        	ALTER TABLE `user_profile` CHANGE `dateField3` `dateField3` DATE NULL;
        	ALTER TABLE `user_profile` CHANGE `dateField4` `dateField4` DATE NULL;
        	ALTER TABLE `user_profile` CHANGE `dateField5` `dateField5` DATE NULL;
        	
        	ALTER TABLE `user_profile` CHANGE `intField1` `intField1` INT(11) NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `intField2` `intField2` INT(11) NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `intField3` `intField3` INT(11) NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `intField4` `intField4` INT(11) NULL DEFAULT NULL;
        	ALTER TABLE `user_profile` CHANGE `intField5` `intField5` INT(11) NULL DEFAULT NULL;");


    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
