<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160718095455 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE IF NOT EXISTS  `file_used` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `type` varchar(32) NOT NULL,
                          `fileId` int(11) NOT NULL COMMENT 'upload_files id',
                          `targetType` varchar(32) NOT NULL,
                          `targetId` int(11) NOT NULL,
                          `createdTime` int(11) NOT NULL,
                          PRIMARY KEY (`id`),
                          KEY `file_used_type_targetType_targetId_index` (`type`,`targetType`,`targetId`),
                          KEY `file_used_type_targetType_targetId_fileId_index` (`type`,`targetType`,`targetId`,`fileId`),
                          KEY `file_used_fileId_index` (`fileId`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
