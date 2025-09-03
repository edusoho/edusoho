<?php

use Phpmig\Migration\Migration;

class ItemBankExerciseAddAgentFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `item_bank_exercise` ADD COLUMN `isAgentActive` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否开启AI伴学助手';
            ALTER TABLE `item_bank_exercise` ADD COLUMN `agentDomainId` varchar(64) NOT NULL DEFAULT '' COMMENT '智能体专业ID';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
           ALTER TABLE `item_bank_exercise` DROP COLUMN `isAgentActive`;
           ALTER TABLE `item_bank_exercise` DROP COLUMN `agentDomainId`;
        ');
    }
}
