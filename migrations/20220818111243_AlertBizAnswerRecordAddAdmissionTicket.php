<?php

use Phpmig\Migration\Migration;

class AlertBizAnswerRecordAddAdmissionTicket extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER  TABLE `biz_answer_record` ADD COLUMN `admission_ticket` varchar(32)  NOT NULL DEFAULT '' COMMENT '考试凭证，只接受最新最新的考试';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
