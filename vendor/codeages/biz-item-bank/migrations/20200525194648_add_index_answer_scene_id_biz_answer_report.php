<?php

use Phpmig\Migration\Migration;

class AddIndexAnswerSceneIdBizAnswerReport extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            ALTER TABLE `biz_answer_report` ADD INDEX(`answer_scene_id`);
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
