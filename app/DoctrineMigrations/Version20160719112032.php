<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160719112032 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if(!$this->isFieldExist('referer_log', 'targetInnerType')){
            $this->addSql("ALTER TABLE referer_log ADD targetInnerType VARCHAR(64) NULL;");
        }
        
        if($this->isFieldExist('referer_log', 'targetInnerType') && $this->isFieldExist('referer_log', 'targetId')){
            $this->addSql("ALTER TABLE referer_log MODIFY COLUMN targetInnerType VARCHAR(64) COMMENT '模块自身的类型' AFTER targetId;");    
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
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
