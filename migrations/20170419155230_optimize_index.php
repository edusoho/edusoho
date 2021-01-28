<?php

use Phpmig\Migration\Migration;

class OptimizeIndex extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('CREATE UNIQUE INDEX theme_config_name_uindex ON theme_config (name);');
        $db->exec('CREATE INDEX announcement_targetType_startTime_endTime_index ON announcement (targetType, startTime, endTime);');
        $db->exec('CREATE INDEX classroom_courses_courseId_index ON classroom_courses (courseId);');
        $db->exec('CREATE INDEX navigation_type_isOpen_orgId_index ON navigation (type, isOpen, orgId);');
        $db->exec('CREATE INDEX tag_owner_ownerType_ownerId_index ON tag_owner (ownerType, ownerId);');
        $db->exec('CREATE INDEX course_task_courseId_status_index ON course_task (courseId, status);');
        $db->exec('CREATE INDEX course_task_result_courseId_userId_index ON course_task_result (courseId, userId);');
        $db->exec('CREATE INDEX course_task_result_courseTaskId_userId_index ON course_task_result (courseTaskId, userId);');
        $db->exec('CREATE INDEX course_favorite_userId_courseSetId_type_index ON course_favorite (userId, courseSetId, type);');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('DROP INDEX theme_config_name_uindex ON theme_config;');
        $db->exec('DROP INDEX announcement_targetType_startTime_endTime_index ON announcement;');
        $db->exec('DROP INDEX classroom_courses_courseId_index ON classroom_courses;');
        $db->exec('DROP INDEX navigation_type_isOpen_orgId_index ON navigation;');
        $db->exec('DROP INDEX tag_owner_ownerType_ownerId_index ON tag_owner;');
        $db->exec('DROP INDEX course_task_courseId_status_index ON course_task;');
        $db->exec('DROP INDEX course_task_result_courseId_userId_index ON course_task_result;');
        $db->exec('DROP INDEX course_task_result_courseTaskId_userId_index ON course_task_result;');
        $db->exec('DROP INDEX course_favorite_userId_courseSetId_type_index ON course_favorite;');
    }
}
