<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150602200953 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `crontab_job`(`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('DeleteSessionJob','everyhour',0,'Topxia\\Service\\User\\Job\\DeleteSessionJob','','1',".time().",0,0,0)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
