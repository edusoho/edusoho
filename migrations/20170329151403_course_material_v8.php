<?php

use Phpmig\Migration\Migration;

class CourseMaterialV8 extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {

        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            CREATE TABLE course_material_v8 AS SELECT * FROM course_material;;
        ");

    }

    /**
     * Undo the migration
     */
    public function down()
    {

        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
           DROP TABLE `course_material_v8`;
        ');

    }
}
