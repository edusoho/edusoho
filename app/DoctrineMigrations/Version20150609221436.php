<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150609221436 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('ip_blacklist');
        $table->addColumn('id', 'integer', array('unsigned'=>true, 'autoincrement'=>true));
        $table->addColumn('ip', 'string', array('length' => 32));
        $table->addColumn('type', 'enum', array('comment' => 'failed,banned'));
        $table->addColumn('counter', 'integer', array('unsigned'=>true, 'default' => 0));
        $table->addColumn('expiredTime', 'integer', array('unsigned'=>true, 'default' => 0));
        $table->addColumn('createdTime', 'integer', array('unsigned'=>true, 'default' => 0));
        $table->setPrimaryKey(array('id'));

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('ip_blacklist');
    }
}
