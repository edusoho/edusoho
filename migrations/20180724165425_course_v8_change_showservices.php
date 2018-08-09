<?php

use Phpmig\Migration\Migration;

class CourseV8ChangeShowservices extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            ALTER TABLE course_v8 alter column `showServices` set default 0;
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
