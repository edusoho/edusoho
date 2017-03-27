<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161009204216 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `im_conversation` ADD UNIQUE(`no`);");
        $this->addSql("ALTER TABLE `im_conversation` ADD INDEX targetId ( `targetId`);");
        $this->addSql("ALTER TABLE `im_conversation` ADD INDEX targetType ( `targetType`);");
        $this->addSql("ALTER TABLE `im_member` ADD INDEX convno_userId ( `convNo`, `userId` );");
        $this->addSql("ALTER TABLE `im_member` ADD INDEX userId_targetType ( `userId`,`targetType` );");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
