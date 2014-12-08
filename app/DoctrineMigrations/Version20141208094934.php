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
			Add column `payPassword` varchar(64) NOT NULL AFTER `salt`;
    	");     	
        $this->addSql(" 
            ALTER table `user` 
            Add column `payPasswordSalt` varchar(64) NOT NULL AFTER `salt`;
        ");  


        $this->addSql(" 
            ALTER table `user` 
            Add column `securityQuestion1` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `securityAnswer1` varchar(64) NOT NULL AFTER `salt`;
        ");   
        $this->addSql(" 
            ALTER table `user` 
            Add column `securityAnswerSalt1` varchar(64) NOT NULL AFTER `salt`;
        ");     
        $this->addSql(" 
            ALTER table `user` 
            Add column `securityQuestion2` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `securityAnswer2` varchar(64) NOT NULL AFTER `salt`;
        ");   
        $this->addSql(" 
            ALTER table `user` 
            Add column `securityAnswerSalt2` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `securityQuestion3` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `securityAnswer3` varchar(64) NOT NULL AFTER `salt`;
        ");   
        $this->addSql(" 
            ALTER table `user` 
            Add column `securityAnswerSalt3` varchar(64) NOT NULL AFTER `salt`;
        ");                                                
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
