<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151225161537 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        if ($this->isFieldExist('course_lesson', 'suggestHours')) {
            $this->addSql("UPDATE `course_lesson` SET `suggestHours` = CEIL(length/3600) WHERE type IN('video','audio') AND length is not Null AND suggestHours=0");
            $this->addSql("UPDATE `course_lesson` SET `suggestHours` = 1 WHERE type IN('video','audio')  AND  length is Null AND suggestHours=0");
            $this->addSql("UPDATE `course_lesson` SET `suggestHours` = 2 WHERE type NOT IN('video','audio') AND  length is Null AND suggestHours=0");
            $this->addSql("UPDATE `course_lesson` SET `suggestHours` = CEIL(length/60) WHERE type IN('live') AND length is not Null AND suggestHours=0");
        }
    }

    /**AND
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
