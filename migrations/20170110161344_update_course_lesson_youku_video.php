<?php

use Phpmig\Migration\Migration;

class UpdateCourseLessonYoukuVideo extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $courseLessons = $db->fetchAll("select * from course_lesson where mediaSource = 'youku'");

        foreach ($courseLessons as $courseLesson) {
            if ($courseLesson['mediaSource'] == 'youku') {
                if (!empty($courseLesson['mediaUri'])) {
                    $correctUri = str_replace('http:', '', $courseLesson['mediaUri']);
                    $correctUri = str_replace('https:', '', $correctUri);

                    $db->exec("update course_lesson set mediaUri='{$correctUri}' where id = '{$courseLesson['id']}'");
                }
            }
        }
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
