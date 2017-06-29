<?php

use Phpmig\Migration\Migration;

class UpdateCategoryDepth extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("UPDATE `category_group` set `depth`= 3 WHERE code = 'course' or code = 'classroom' ;");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
