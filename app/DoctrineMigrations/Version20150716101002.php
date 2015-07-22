<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150716101002 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if($this->isTableExist('homework')) {
            $this->addSql("delete from homework where lessonId not in (select id from course_lesson);");
        }
        if($this->isTableExist('exercise')){
            $this->addSql("delete from exercise where lessonId not in (select id from course_lesson);");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
