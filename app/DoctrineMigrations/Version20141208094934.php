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
            Add column `question1` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `answer1` varchar(64) NOT NULL AFTER `salt`;
        ");   
        $this->addSql(" 
            ALTER table `user` 
            Add column `answerSalt1` varchar(64) NOT NULL AFTER `salt`;
        ");     
        $this->addSql(" 
            ALTER table `user` 
            Add column `question2` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `answer2` varchar(64) NOT NULL AFTER `salt`;
        ");   
        $this->addSql(" 
            ALTER table `user` 
            Add column `answerSalt2` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `question3` varchar(64) NOT NULL AFTER `salt`;
        ");  
        $this->addSql(" 
            ALTER table `user` 
            Add column `answer3` varchar(64) NOT NULL AFTER `salt`;
        ");   
        $this->addSql(" 
            ALTER table `user` 
            Add column `answerSalt3` varchar(64) NOT NULL AFTER `salt`;
        ");                                                
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
