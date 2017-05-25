<?php

use Phpmig\Migration\Migration;

class AddCourseType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
           alter table course_v8 add column  `courseType` varchar(32) DEFAULT 'default' COMMENT 'default, normal, times,...';
           update course_v8 set courseType = case when isDefault = 1 then  'default' else 'normal' end;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
