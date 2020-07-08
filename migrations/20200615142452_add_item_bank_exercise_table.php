<?php

use Phpmig\Migration\Migration;

class AddItemBankExerciseTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $container['db']->exec("
            CREATE TABLE `item_bank_assessment_exercise` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `exerciseId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题库练习id',
              `moduleId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '模块id',
              `assessmentId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '试卷id',
              `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `moduleId` (`moduleId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷练习设置';
            
            CREATE TABLE `item_bank_assessment_exercise_record` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `exerciseId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题库练习id',
              `moduleId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '模块id',
              `assessmentId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '试卷id',
              `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
              `answerRecordId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '答题记录id',
              `status` enum('doing','paused','reviewing','finished') NOT NULL DEFAULT 'doing' COMMENT '答题状态',
              `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `answerRecordId` (`answerRecordId`),
              KEY `moduleId` (`moduleId`,`userId`),
              KEY `userId` (`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷练习记录表';
            
            CREATE TABLE `item_bank_chapter_exercise_record` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `moduleId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '模块id',
              `exerciseId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题库练习id',
              `itemCategoryId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题目分类id',
              `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
              `status` enum('doing','paused','reviewing','finished') NOT NULL DEFAULT 'doing' COMMENT '答题状态',
              `answerRecordId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '答题记录id',
              `questionNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问题总数',
              `doneQuestionNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '回答题目数',
              `rightQuestionNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '答题问题数',
              `rightRate` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '正确率',
              `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `moduleId` (`moduleId`),
              KEY `userId` (`userId`),
              KEY `answerRecordId` (`answerRecordId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库章节练习记录';
            
            CREATE TABLE `item_bank_exercise` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `seq` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '序号',
              `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
              `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '状态  draft, published, closed',
              `chapterEnable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '章节练习是否开启',
              `assessmentEnable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '试卷练习是否开启',
              `questionBankId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '资源题库id',
              `categoryId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题库分类id',
              `cover` varchar(1024) NOT NULL DEFAULT '' COMMENT '封面图',
              `studentNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学员总数',
              `teacherIds` varchar(1024) NOT NULL DEFAULT '' COMMENT '教师ID列表',
              `joinEnable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许加入',
              `vipLevelId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支持的vip等级',
              `expiryMode` varchar(32) NOT NULL DEFAULT 'forever' COMMENT '过期方式 days,date,end_date,forever',
              `expiryDays` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '过期天数',
              `expiryStartDate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '有效期开始时间',
              `expiryEndDate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '有效期结束时间',
              `isFree` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否免费1表示免费',
              `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '总收入',
              `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
              `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
              `ratingNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
              `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '评分',
              `recommended` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐',
              `recommendedSeq` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
              `recommendedTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
              `creator` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
              `createdTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updatedTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改时间',
              PRIMARY KEY (`id`),
              KEY `questionBankId` (`questionBankId`),
              KEY `categoryId` (`categoryId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库练习表';
            
            CREATE TABLE `item_bank_exercise_member` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `exerciseId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题库练习id',
              `questionBankId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '资源题库id',
              `joinedType` enum('buy','admin') NOT NULL DEFAULT 'buy' COMMENT '加入方式 buy=购买 admin=后台添加',
              `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户Id',
              `orderId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
              `deadline` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学习最后期限',
              `doneQuestionNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '相对当前题库的已做问题总数',
              `rightQuestionNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '相对当前题库的做对问题总数',
              `masteryRate` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '相对当前题库的掌握度',
              `completionRate` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '相对当前题库的完成率',
              `role` enum('student','teacher') NOT NULL DEFAULT 'student' COMMENT '成员角色',
              `locked` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '学员是否锁定',
              `remark` varchar(255) NOT NULL COMMENT '备注',
              `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `exerciseId` (`exerciseId`),
              KEY `userId` (`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库练习成员';
            
            CREATE TABLE `item_bank_exercise_member_operation_record` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(1024) NOT NULL DEFAULT '' COMMENT '题库练习名称',
              `memberId` int(10) unsigned NOT NULL COMMENT '成员ID',
              `memberType` varchar(32) NOT NULL DEFAULT 'student' COMMENT '成员身份',
              `exerciseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题库练习ID',
              `operateType` enum('join','exit') NOT NULL DEFAULT 'join' COMMENT '操作类型（join=加入, exit=退出）',
              `operateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
              `operatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户ID',
              `userId` int(11) NOT NULL DEFAULT '0' COMMENT '用户Id',
              `orderId` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
              `refundId` int(11) NOT NULL DEFAULT '0' COMMENT '退款ID',
              `reason` varchar(256) NOT NULL DEFAULT '' COMMENT '加入理由或退出理由',
              `reasonType` varchar(255) NOT NULL DEFAULT '' COMMENT '用户退出或加入的类型',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `exerciseId` (`exerciseId`),
              KEY `userId` (`userId`),
              KEY `operateType` (`operateType`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE `item_bank_exercise_module` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `seq` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '序号',
              `exerciseId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题库练习id',
              `answerSceneId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '场次id',
              `title` varchar(255) NOT NULL DEFAULT '' COMMENT '模块标题',
              `type` enum('chapter','assessment') NOT NULL DEFAULT 'chapter' COMMENT '模块类型',
              `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `exerciseId` (`exerciseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='练习模块表';
            
            CREATE TABLE `item_bank_exercise_question_record` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `exerciseId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题库练习id',
              `moduleId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '模块id',
              `itemId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
              `questionId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问题id',
              `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
              `status` enum('right','wrong') NOT NULL DEFAULT 'wrong' COMMENT '状态',
              `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `moduleId` (`moduleId`),
              KEY `userId` (`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='已做题目表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $container['db']->exec('
            DROP TABLE IF EXISTS `item_bank_assessment_exercise`;
            DROP TABLE IF EXISTS `item_bank_assessment_exercise_record`;
            DROP TABLE IF EXISTS `item_bank_chapter_exercise_record`;
            DROP TABLE IF EXISTS `item_bank_exercise`;
            DROP TABLE IF EXISTS `item_bank_exercise_member`;
            DROP TABLE IF EXISTS `item_bank_exercise_member_operation_record`;
            DROP TABLE IF EXISTS `item_bank_exercise_module`;
            DROP TABLE IF EXISTS `item_bank_exercise_question_record`;
        ');
    }
}
