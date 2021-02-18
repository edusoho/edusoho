<?php

use Phpmig\Migration\Migration;

class AddIndexAssessmentIdSeqBizAssessmentSectionItem extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            ALTER TABLE `biz_assessment_section_item` ADD INDEX assessmentId_seq(`assessment_id`, `seq`);
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
