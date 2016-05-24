<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160420160332 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `file` ADD `uploadFileId` INT(10) NULL AFTER `createdTime`;");
        $this->addSql("insert into `file` (groupId, userId, uri, size, createdTime,mime, uploadFileId) select (select id from file_group where name='默认文件组') as groupId, createdUserId as userId, concat('public://',hashId) as uri, fileSize as size, createdTime, type as mime, id as uploadFileId from upload_files where isPublic=1;");
        $this->addSql("delete from upload_files where isPublic=1;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
