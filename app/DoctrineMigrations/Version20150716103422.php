<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150716103422 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `crontab_job`(`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('删除过期的token','everyhour',0,'Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob','',0,".time().",0,0,0)");

        if(!$this->isCrontabJobExist("DeleteSessionJob")){
            $this->addSql("INSERT INTO `crontab_job`(`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('DeleteSessionJob','everyhour',0,'Topxia\\\\Service\\\\User\\\\Job\\\\DeleteSessionJob','',0,".time().",0,0,0)");
        } else {
            $this->addSql("UPDATE `crontab_job` SET `jobClass`='Topxia\\\\Service\\\\User\\\\Job\\\\DeleteSessionJob' WHERE `name`='DeleteSessionJob';");
        }
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
