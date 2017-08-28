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
        $db->exec("ALTER TABLE `biz_site_cashflow` Add column `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
