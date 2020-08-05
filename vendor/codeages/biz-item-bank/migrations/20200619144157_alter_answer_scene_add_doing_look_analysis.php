<?php

use Phpmig\Migration\Migration;

class AlterAnswerSceneAddDoingLookAnalysis extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            ALTER TABLE `biz_answer_scene` ADD `doing_look_analysis` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '支持做题中查看解析' AFTER `start_time`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
