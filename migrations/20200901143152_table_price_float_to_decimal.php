<?php

use Phpmig\Migration\Migration;

class TablePriceFloatToDecimal extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_v8` 
            modify `price` decimal(12,2) DEFAULT '0.00' COMMENT '课程的价格',
            modify `income` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总收入',
            modify `originPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '课程人民币原价',
            modify `coinPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币价格',
            modify `originCoinPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '课程虚拟币原价';
        ");

        $biz['db']->exec("
            ALTER TABLE `course_set_v8`
            modify `maxCoursePrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最高价格',
            modify `minCoursePrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最低价格';
        ");

        $biz['db']->exec("
            ALTER TABLE `classroom`
            modify `price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
            modify `income` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '收入';
        ");

        $biz['db']->exec("
            ALTER TABLE `item_bank_exercise`
            modify `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
            modify `originPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
            modify `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '总收入';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_v8` 
            modify `price` float(10,2) DEFAULT '0.00' COMMENT '课程的价格',
            modify `income` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总收入',
            modify `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程人民币原价',
            modify `coinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币价格',
            modify `originCoinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程虚拟币原价';
        ");

        $biz['db']->exec("
            ALTER TABLE `course_set_v8`
            modify `maxCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最高价格',
            modify `minCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最低价格';
        ");

        $biz['db']->exec("
            ALTER TABLE `classroom`
            modify `price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
            modify `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '收入';
        ");
        $biz['db']->exec("
            ALTER TABLE `item_bank_exercise`
            modify `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '总收入',
            modify `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
            modify `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价';
        ");
    }
}
