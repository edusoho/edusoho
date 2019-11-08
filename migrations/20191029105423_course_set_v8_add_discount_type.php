<?php

use Phpmig\Migration\Migration;

class CourseSetV8AddDiscountType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("ALTER TABLE `course_set_v8` ADD `discountType` varchar(64) NOT NULL DEFAULT 'discount' COMMENT '打折类型(discount:打折，reduce:减价)' AFTER `discountId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `course_set_v8` DROP column `discountType`;');
    }
}
