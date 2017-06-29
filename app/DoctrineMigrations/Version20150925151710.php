<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150925151710 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM `crontab_job` where name='DeleteExpiredTokenJob';");
        $this->addSql("DELETE FROM `crontab_job` where name='CancelOrderJob';");

        $this->addSql("INSERT INTO `crontab_job` (`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) 
            VALUES
        ('CancelOrderJob', 'everyhour', '0', 'Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob', '', 0, 1440528069, 1440524469, 0, 0),
        ('DeleteExpiredTokenJob', 'everyhour', '0', 'Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob', '', 0, 1440528062, 1440524462, 0, 0);");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
