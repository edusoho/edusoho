<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150910143701 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if(!$this->isFieldExist('crontab_job', 'targetType')){
            $this->addSql("ALTER TABLE  `crontab_job` ADD  `targetType` VARCHAR( 64 ) NOT NULL DEFAULT  '' AFTER  `jobParams`");
        }

        if(!$this->isFieldExist('crontab_job', 'targetId')){
            if(!$this->isFieldExist('crontab_job', 'targetType')){
                $this->addSql("ALTER TABLE  `crontab_job` ADD  `targetId` INT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `targetType`");
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
