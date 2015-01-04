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
            Add column `payPasswordSalt` varchar(64) NOT NULL DEFAULT '' AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `payPassword` varchar(64) NOT NULL DEFAULT '' AFTER `salt`;
        "); 

        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `user_secure_question` (
        `id` int(10) unsigned NOT NULL auto_increment ,
        `userId` int(10) unsigned NOT NULL DEFAULT 0,
        `securityQuestionCode` varchar(64) NOT NULL DEFAULT '',
        `securityAnswer` varchar(64) NOT NULL DEFAULT '',
        `securityAnswerSalt` varchar(64) NOT NULL DEFAULT '',
        `createdTime` int(10) unsigned NOT NULL DEFAULT '0',       
        PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"); 

    }


    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
