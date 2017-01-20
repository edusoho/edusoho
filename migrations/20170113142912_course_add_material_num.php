<?php

use Phpmig\Migration\Migration;

class CourseAddMaterialNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        //

        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `c2_course` ADD COLUMN  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量';
            ALTER TABLE `c2_course_set` ADD COLUMN  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `c2_course` DROP COLUMN  `materialNum` ;
            ALTER TABLE `c2_course_set` DROP COLUMN  `materialNum`;
        ");
    }
}
