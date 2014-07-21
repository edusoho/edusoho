<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140721142005 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE  `upload_files` CHANGE  `convertParams`  `convertParams` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文件转换参数'");
    	$this->addSql("ALTER TABLE  `theme_config` CHANGE  `config`  `config` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE  `confirmConfig`  `confirmConfig` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
CHANGE  `allConfig`  `allConfig` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
