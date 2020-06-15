<?php

use Phpmig\Migration\Migration;

class CreateItemBankExerciseTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
        CREATE TABLE `item_bank_exercise` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `seq` int(11) NOT NULL DEFAULT '0' COMMENT '序号',
          `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
          `questionBankId` int(11) NOT NULL DEFAULT '0' COMMENT '资源题库id',
          `categoryId` int(11) NOT NULL DEFAULT '0' COMMENT '题库分类id',
          `cover` varchar(1024) NOT NULL DEFAULT '' COMMENT '封面图',
          `studentNum` int(11) NOT NULL DEFAULT '0' COMMENT '学员总数',
          `teacherIds` varchar(1024) NOT NULL DEFAULT '' COMMENT '教师ID列表',
          `expiryMode` varchar(32) NOT NULL DEFAULT '' COMMENT '过期方式 days, date',
          `expiryDays` int(11) NOT NULL DEFAULT '0' COMMENT '过期天数',
          `expiryStartDate` int(11) NOT NULL DEFAULT '0' COMMENT '有效期开始时间',
          `expiryEndDate` int(11) NOT NULL DEFAULT '0' COMMENT '有效期结束时间',
          `isFree` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否免费1表示免费',
          `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '总收入',
          `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
          `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
          `coinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币售价',
          `originCoinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币原价',
          `recommended` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
          `recommendedSeq` int(11) NOT NULL DEFAULT '0' COMMENT '推荐序号',
          `recommendedTime` int(11) NOT NULL DEFAULT '0' COMMENT '推荐时间',
          `createdTime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
          `updatedTime` int(11) NOT NULL DEFAULT '0' COMMENT '最后修改时间',
          `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '状态  draft, published, closed',
          PRIMARY KEY (`id`),
          KEY `questionBankId` (`questionBankId`),
          KEY `categoryId` (`categoryId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库练习表';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `item_bank_exercise`');
    }
}
