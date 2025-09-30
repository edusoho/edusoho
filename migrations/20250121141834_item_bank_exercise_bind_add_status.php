<?php

use Phpmig\Migration\Migration;

class itemBankExerciseBindAddStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `item_bank_exercise_bind` ADD COLUMN `status` VARCHAR(16) DEFAULT 'finished' COMMENT '绑定状态' AFTER `bindType`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `item_bank_exercise_bind` DROP COLUMN `status`;');
    }
}
