<?php

use Phpmig\Migration\Migration;

class CourseDraftChangeLessonId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("ALTER TABLE course_draft CHANGE lessonId activityId INT(10) unsigned NOT NULL COMMENT '教学活动ID';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec("ALTER TABLE course_draft CHANGE activityId lessonId INT(10) unsigned NOT NULL COMMENT '课时ID';");
    }
}
