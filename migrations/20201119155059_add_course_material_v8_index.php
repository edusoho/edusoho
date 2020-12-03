<?php

use Phpmig\Migration\Migration;

class AddCourseMaterialV8Index extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            CREATE INDEX lessonId_type ON course_material_v8 (`lessonId`, `type`);
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP INDEX lessonId_type ON course_material_v8;
        ');
    }
}
