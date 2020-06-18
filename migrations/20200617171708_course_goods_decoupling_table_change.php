<?php

use Phpmig\Migration\Migration;

class CourseGoodsDecouplingTableChange extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `` ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
