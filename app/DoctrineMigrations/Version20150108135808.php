<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150108135808 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        $this->addSql("	
            ALTER TABLE `course` 
            ADD COLUMN `originPrice` FLOAT(10,2) NOT NULL DEFAULT  0  AFTER `price`;
    	");  
        $this->addSql("	
            UPDATE `course` SET `originPrice`=`price` where id>=0;
    	"); 
    	$this->addSql("	
            ALTER TABLE `course` 
            ADD COLUMN `originCoinPrice` FLOAT(10,2) NOT NULL DEFAULT  0  AFTER `coinPrice`;
    	"); 
        $this->addSql("	
            UPDATE `course` SET `originCoinPrice`=`coinPrice` where id>=0;
    	");     	
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
