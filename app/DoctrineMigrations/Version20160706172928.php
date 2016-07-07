<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160706172928 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if ($schema->hasTable('announcement')) {
            $table = $schema->getTable('announcement');
            if (!$table->hasColumn('copyId')) {
                $table->addColumn('copyId', Type::INTEGER, array(
                    'length'  => 10,
                    'notnull' => true,
                    'default' => '0',
                    'comment' => '复制的公告ID'
                ));
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
}
