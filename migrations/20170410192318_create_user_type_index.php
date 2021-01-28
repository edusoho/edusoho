<?php

use Phpmig\Migration\Migration;

class CreateUserTypeIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('CREATE INDEX user_type_index ON user (type);');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('DROP INDEX user_type_index ON user;');
    }
}
