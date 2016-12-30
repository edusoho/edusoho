<?php

use Phpmig\Migration\Migration;

class C2CourseChangePrice extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE `c2_course` CHANGE `price` `price` FLOAT(10,2) NULL DEFAULT '0';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE `c2_course` CHANGE `price` `price` int(11) NULL DEFAULT '0';");
    }
}
