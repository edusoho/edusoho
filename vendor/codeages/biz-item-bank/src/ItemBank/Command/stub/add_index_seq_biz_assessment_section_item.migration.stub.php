<?php

use Phpmig\Migration\Migration;

class AddIndexSeqBizAssessmentSectionItem extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            ALTER TABLE `biz_assessment_section_item` ADD INDEX(`seq`);
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
