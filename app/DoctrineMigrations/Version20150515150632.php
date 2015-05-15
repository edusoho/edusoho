<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150515150632 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `crontab_job` (`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('CancelOrderJob', 'everyday', '01:00:00', 'Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob', '', '0', '0', '0', '0', '0');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
