<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150909163344 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->isTableExist('sessions')){
            $this->addSql("CREATE TABLE `sessions` (
                    `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
                    `sess_user_id` INT UNSIGNED NOT NULL DEFAULT  '0',
                    `sess_data` BLOB NOT NULL,
                    `sess_time` INTEGER UNSIGNED NOT NULL,
                    `sess_lifetime` MEDIUMINT NOT NULL
                ) COLLATE utf8_bin, ENGINE = InnoDB;");
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
