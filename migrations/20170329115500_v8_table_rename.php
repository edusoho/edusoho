<?php

use Phpmig\Migration\Migration;

class V8TableRename extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $this->renameTable('c2_course', 'course_v8');
        $this->renameTable('c2_course_set', 'course_set_v8');
        $this->renameTable('video_activity', 'activity_video');
        $this->renameTable('doc_activity', 'activity_doc');
        $this->renameTable('audio_activity', 'activity_audio');
        $this->renameTable('ppt_activity', 'activity_ppt');
        $this->renameTable('flash_activity', 'activity_flash');
        $this->renameTable('live_activity', 'activity_live');
        $this->renameTable('download_activity', 'activity_download');
        $this->renameTable('text_activity', 'activity_text');
        $this->renameTable('testpaper_activity', 'activity_testpaper');
    }

    protected function renameTable($from, $to)
    {
        $biz = $this->getContainer();
        if ($this->isTableExist($from)) {
            $biz['db']->exec("
                ALTER TABLE `{$from}` RENAME TO `{$to}`;
            ");
        }
    }

    protected function isTableExist($table)
    {
        $biz = $this->getContainer();
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $this->renameTable('course_v8', 'c2_course');
        $this->renameTable('course_set_v8', 'c2_course_set');
        $this->renameTable('activity_video', 'video_activity');
        $this->renameTable('activity_doc', 'doc_activity');
        $this->renameTable('activity_audio', 'audio_activity');
        $this->renameTable('activity_ppt', 'ppt_activity');
        $this->renameTable('activity_flash', 'flash_activity');
        $this->renameTable('activity_live', 'live_activity');
        $this->renameTable('activity_download', 'download_activity');
        $this->renameTable('activity_text', 'text_activity');
        $this->renameTable('activity_testpaper', 'testpaper_activity');
    }
}
