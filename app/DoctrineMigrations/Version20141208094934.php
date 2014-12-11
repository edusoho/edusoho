<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141208094934 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	
        $this->addSql(" 
            ALTER table `user` 
            Add column `payPasswordSalt` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `payPassword` varchar(64) NOT NULL AFTER `salt`;
        "); 

        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `user_secure_question` (
        `id` int(10) unsigned NOT NULL auto_increment ,
        `userId` int(10) unsigned NOT NULL DEFAULT 0,
        `securityQuestion1` varchar(64) NOT NULL DEFAULT '',
        `securityAnswer1` varchar(64) NOT NULL DEFAULT '',
        `securityAnswerSalt1` varchar(64) NOT NULL DEFAULT '',
        `securityQuestion2` varchar(64) NOT NULL DEFAULT '',
        `securityAnswer2` varchar(64) NOT NULL DEFAULT '',
        `securityAnswerSalt2` varchar(64) NOT NULL DEFAULT '',
        `securityQuestion3` varchar(64) NOT NULL  DEFAULT '',
        `securityAnswer3` varchar(64) NOT NULL DEFAULT '',
        `securityAnswerSalt3` varchar(64) NOT NULL DEFAULT '',        
        
        PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"); 

    }


    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
