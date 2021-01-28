<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160518151303 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `org` (`id`, `name`, `parentId`, `childrenNum`, `depth`, `seq`, `description`, `code`, `orgCode`, `createdUserId`, `createdTime`, `updateTime`) VALUES (1, '全站', 0, 0, 1, 0, '', 'FullSite', '1.', 1, 1463555406, 0);");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
