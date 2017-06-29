<?php

use Phpmig\Migration\Migration;

class TestpaperAddCourseset extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        /*$db->exec("
    ALTER TABLE testpaper ADD courseSetId int(11) UNSIGNED NOT NULL DEFAULT '0';
    ALTER TABLE testpaper_result ADD courseSetId int(11) UNSIGNED NOT NULL DEFAULT '0'
    ");*/
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        /*$db->exec('
    ALTER TABLE testpaper DROP COLUMN courseSetId;
    ALTER TABLE testpaper_result DROP COLUMN courseSetId;
    ');*/
    }
}
