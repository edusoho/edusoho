<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150818192323 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql("UPDATE friend SET pair=1 WHERE id IN ( SELECT a.id id FROM (SELECT id,fromId,toId FROM friend) AS a, (SELECT id,fromId,toId FROM friend) AS b WHERE a.fromId=b.toId AND a.toId=b.fromId)");
        $this->addSql("UPDATE friend f1,friend f2 set f1.pair=1 where f1.fromId=f2.toId and f1.toId=f2.fromId");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
