<?php

use Phpmig\Migration\Migration;

class SiteIncomeRename extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("RENAME TABLE site_income TO site_cashflow");
        $db->exec("ALTER TABLE `site_cashflow` Add column `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
