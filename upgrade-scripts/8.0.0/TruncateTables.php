<?php

class TruncateTables extends AbstractMigrate
{
    public function update($page)
    {
        $tables = array(
            'activity',
            'activity_audio',
            'activity_doc',
            'activity_download',
            'activity_flash',
            'activity_learn_log',
            'activity_live',
            'activity_ppt',
            'activity_text',
            'activity_testpaper',
            'activity_video',
            'course_material_v8',
            'course_set_v8',
            'course_v8',
            'testpaper_item_result_v8',
            'testpaper_item_v8',
            'testpaper_result_v8',
            'testpaper_v8',
            'course_task',
            'course_task_result',
            'course_task_view',
        );

        foreach ($tables as $table) {
            if ($this->isTableExist($table)) {
                $this->exec("TRUNCATE TABLE {$table};");
            }
        }
    }
}
