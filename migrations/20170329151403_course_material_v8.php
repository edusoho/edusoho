<?php

use Phpmig\Migration\Migration;

class CourseMaterialV8 extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
            ALTER TABLE `course_material` RENAME TO `course_material_v8`;
        ');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
           ALTER TABLE `course_material_v8` RENAME TO `course_material`;
        ');
    }
}
