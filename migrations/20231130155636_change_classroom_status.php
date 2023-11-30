<?php

use Phpmig\Migration\Migration;

class ChangeClassroomStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `classroom` 
            MODIFY COLUMN `status` enum('closed','draft','published','unpublished') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布，下架' AFTER `subtitle`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `classroom` 
            MODIFY COLUMN `status` enum('closed','draft','published') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布' AFTER `subtitle`;
        ");
    }
}
