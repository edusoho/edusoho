<?php

use Phpmig\Migration\Migration;

class Coursev8AddCoursesetidIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            ALTER TABLE `course_v8` ADD INDEX `courseset_id_index` (`courseSetId`);
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
