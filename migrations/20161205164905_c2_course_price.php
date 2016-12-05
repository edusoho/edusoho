<?php

use Phpmig\Migration\Migration;

class C2CoursePrice extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE `c2_course` CHANGE `price` `price` float(10,2) NOT NULL DEFAULT 0");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE `c2_course` CHANGE `price` `price` int(10) NOT NULL DEFAULT 0");
    }
}
