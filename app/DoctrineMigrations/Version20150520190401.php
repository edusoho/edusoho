<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150520190401 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($this->isFieldExist('course_note_like', 'truename')) {
            $this->addSql("ALTER TABLE course_note_like DROP COLUMN truename;");
        }
        
    }

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
