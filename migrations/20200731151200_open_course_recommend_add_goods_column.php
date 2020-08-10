<?php

use Phpmig\Migration\Migration;

class OpenCourseRecommendAddGoodsColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `open_course_recommend` ADD COLUMN `recommendGoodsId` int(10) NOT NULL COMMENT '推荐商品id' AFTER `recommendCourseId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `open_course_recommend` DROP COLUMN `recommendGoodsId`;');
    }
}
