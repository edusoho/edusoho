<?php

use Phpmig\Migration\Migration;

class UpdateConvertStatusJob extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $expression = rand(0, 15).'/15 * * * *';
        $biz['db']->exec("
            UPDATE `biz_scheduler_job` SET expression = '{$expression}' WHERE name = 'CheckConvertStatusJob';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            UPDATE `biz_scheduler_job` SET expression = '*/15 * * * *' WHERE name = 'CheckConvertStatusJob';
        ");
    }
}
