<?php

use Phpmig\Migration\Migration;

class C2CourseRemoveAudit extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course` DROP COLUMN auditStatus;
            ALTER TABLE `c2_course` DROP COLUMN auditRemark;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course` ADD COLUMN auditStatus VARCHAR(32) COMMENT 'draft, committed, rejected, accepted';
            ALTER TABLE `c2_course` ADD COLUMN auditRemark TEXT;
        ");
    }
}
