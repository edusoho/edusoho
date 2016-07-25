<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        if (!$this->isTableExist('open_course_recommend')) {
            $this->getConnection()->exec("
                CREATE TABLE `open_course_recommend` (
                 `id` int(10) NOT NULL AUTO_INCREMENT,
                 `openCourseId` int(10) NOT NULL COMMENT '公开课id',
                 `recommendCourseId` int(10) NOT NULL DEFAULT '0' COMMENT '推荐课程id',
                 `seq` int(10) NOT NULL DEFAULT '0' COMMENT '序列',
                 `type` varchar(255) NOT NULL COMMENT '类型',
                 `createdTime` int(10) NOT NULL COMMENT '创建时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公开课推荐课程表'
            ");
        }

        if (!$this->isTableExist('open_course')) {
            $this->getConnection()->exec("
                CREATE TABLE `open_course` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程ID',
                  `title` varchar(1024) NOT NULL COMMENT '课程标题',
                  `subtitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '课程副标题',
                  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft' COMMENT '课程状态',
                  `type` varchar(255) NOT NULL DEFAULT 'normal' COMMENT '课程类型',
                  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
                  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
                  `tags` text COMMENT '标签IDs',
                  `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
                  `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
                  `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
                  `about` text COMMENT '简介',
                  `teacherIds` text COMMENT '显示的课程教师IDs',
                  `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
                  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
                  `likeNum` int(10) NOT NULL DEFAULT '0' COMMENT '点赞数',
                  `postNum` int(10) NOT NULL DEFAULT '0' COMMENT '评论数',
                  `userId` int(10) unsigned NOT NULL COMMENT '课程发布人ID',
                  `parentId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程的父Id',
                  `locked` INT(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁',
                  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
                  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
                  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '课程创建时间',
                  `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

            ");
            $this->getConnection()->exec("ALTER TABLE `open_course` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if (!$this->isTableExist('open_course_lesson')) {
            $this->getConnection()->exec("
                CREATE TABLE `open_course_lesson` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课时ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '课时所属课程ID',
                  `chapterId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时所属章节ID',
                  `number` int(10) unsigned NOT NULL COMMENT '课时编号',
                  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时在课程中的序号',
                  `free` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为免费课时',
                  `status` enum('unpublished','published') NOT NULL DEFAULT 'published' COMMENT '课时状态',
                  `title` varchar(255) NOT NULL COMMENT '课时标题',
                  `summary` text COMMENT '课时摘要',
                  `tags` text COMMENT '课时标签',
                  `type` varchar(64) NOT NULL DEFAULT 'text' COMMENT '课时类型',
                  `content` text COMMENT '课时正文',
                  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课时获得的学分',
                  `requireCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习课时前，需达到的学分',
                  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
                  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
                  `mediaName` varchar(255) NOT NULL DEFAULT '' COMMENT '媒体文件名称',
                  `mediaUri` text COMMENT '媒体文件资源名',
                  `homeworkId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '作业iD',
                  `exerciseId` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT '练习ID',
                  `length` int(11) unsigned DEFAULT NULL COMMENT '时长',
                  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
                  `quizNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '测验题目数量',
                  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学的学员数',
                  `viewedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看数',
                  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时开始时间',
                  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时结束时间',
                  `memberNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时加入人数',
                  `replayStatus` enum('ungenerated','generating','generated') NOT NULL DEFAULT 'ungenerated',
                  `maxOnlineNum` INT NULL DEFAULT '0' COMMENT '直播在线人数峰值',
                  `liveProvider` int(10) unsigned NOT NULL DEFAULT '0',
                  `userId` int(10) unsigned NOT NULL COMMENT '发布人ID',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id',
                  `suggestHours` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '建议学习时长',
                  `testMode` ENUM('normal', 'realTime') NULL DEFAULT 'normal' COMMENT '考试模式',
                  `testStartTime` INT(10) NULL DEFAULT '0' COMMENT '实时考试开始时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
            $this->addSql("ALTER TABLE `open_course_lesson` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if (!$this->isTableExist('open_course_member')) {
            $this->getConnection()->exec("
                CREATE TABLE `open_course_member` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程学员记录ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员ID',
                  `mobile` varchar(32) NOT NULL DEFAULT  '' COMMENT '手机号码',
                  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学课时数',
                  `learnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习时间',
                  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
                  `isVisible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '可见与否，默认为可见',
                  `role` enum('student','teacher') NOT NULL DEFAULT 'student' COMMENT '课程会员角色',
                  `ip` varchar(64) COMMENT 'IP地址',
                  `lastEnterTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次进入时间',
                  `isNotified` int(10) NOT NULL DEFAULT '0' COMMENT '直播开始通知',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '学员加入课程时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('course_favorite', 'type')) {
            $this->getConnection()->exec("ALTER TABLE course_favorite ADD `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型';");
        }

        if (!$this->isFieldExist('course_lesson_replay', 'type')) {
            $this->getConnection()->exec("ALTER TABLE course_lesson_replay ADD `type` varchar(50) NOT NULL DEFAULT 'live' COMMENT '课程类型';");
        }

        if (!$this->isFieldExist('course_material', 'type')) {
            $this->getConnection()->exec("ALTER TABLE course_material ADD `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型';");
        }

        if (!$this->isFieldExist('upload_files', 'description')) {
            $this->getConnection()->exec("ALTER TABLE `upload_files` ADD `description` text DEFAULT NUll AFTER `length`;");
        }

        if (!$this->isTableExist('referer_log')) {
            $this->getConnection()->exec("
                CREATE TABLE `referer_log` (
                  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `targetId` int(11) NOT NULL COMMENT '模块ID',
                  `targetType` varchar(64) NOT NULL COMMENT '模块类型',
                  `targetInnerType` VARCHAR(64) NULL COMMENT '模块自身的类型',
                  `sourceUrl`  varchar(255) DEFAULT '' COMMENT '访问来源Url',
                  `sourceHost` varchar(80)  DEFAULT '' COMMENT '访问来源HOST',
                  `sourceName` varchar(64)  DEFAULT '' COMMENT '访问来源站点名称',
                  `orderCount` int(10) unsigned  DEFAULT '0'  COMMENT '促成订单数',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问时间',
                  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问者',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='模块(课程|班级|公开课|...)的访问来源日志';
            ");
        }

        if (!$this->isTableExist('order_referer_log')) {
            $this->getConnection()->exec("
                CREATE TABLE `order_referer_log` (
                  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `refererLogId` int(11) NOT NULL COMMENT '促成订单的访问日志ID',
                  `orderId` int(10) unsigned  DEFAULT '0'  COMMENT '订单ID',
                  `sourceTargetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
                  `sourceTargetType` varchar(64) NOT NULL DEFAULT '' COMMENT '来源类型',
                  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '订单的对象类型',
                  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的对象ID',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '订单支付时间',
                  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '订单支付者',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单促成日志';
            ");
        }

        if ($this->isFieldExist('referer_log', 'sourceUrl')) {
            $this->getConnection()->exec("ALTER TABLE `referer_log` CHANGE `sourceUrl` `refererUrl` text NOT NULL  COMMENT '访问来源Url';");
        }

        if ($this->isFieldExist('referer_log', 'sourceHost')) {
            $this->getConnection()->exec("ALTER TABLE `referer_log` CHANGE `sourceHost` `refererHost` VARCHAR(80)  NOT NULL COMMENT '访问来源HOST';");
        }

        if ($this->isFieldExist('referer_log', 'sourceName')) {
            $this->getConnection()->exec("ALTER TABLE `referer_log` CHANGE `sourceName` `refererName` VARCHAR(64)  DEFAULT NUll  COMMENT '访问来源站点名称';");
        }

        if (!$this->isFieldExist('referer_log', 'updatedTime')) {
            $this->getConnection()->exec("ALTER TABLE `referer_log` ADD `updatedTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '更新时间';");
        }

        if ($this->isFieldExist('order_referer_log', 'createdUser')) {
            $this->getConnection()->exec("ALTER TABLE `order_referer_log` CHANGE `createdUser` `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付者';");
        }

        if ($this->isFieldExist('referer_log', 'targertId')) {
            $this->getConnection()->exec(" ALTER TABLE `referer_log` CHANGE `targertId` `targetId` VARCHAR(64)  DEFAULT NUll  COMMENT '访问来源站点名称';");
        }

        if ($this->isFieldExist('referer_log', 'targertType')) {
            $this->getConnection()->exec("ALTER TABLE `referer_log` CHANGE `targertType` `targetType` VARCHAR(64)  DEFAULT NUll  COMMENT '访问来源站点名称';");
        }

        if (!$this->isFieldExist('order_referer_log', 'sourceTargetId')) {
            $this->getConnection()->exec("ALTER TABLE `order_referer_log` ADD `sourceTargetId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '来源ID' ;");
        }

        if (!$this->isFieldExist('order_referer_log', 'sourceTargetType')) {
            $this->getConnection()->exec("ALTER TABLE `order_referer_log` ADD `sourceTargetType` varchar(64) NOT NULL DEFAULT ''  COMMENT '来源类型';");
        }

        if (!$this->isFieldExist('referer_log', 'targetInnerType')) {
            $this->getConnection()->exec("ALTER TABLE referer_log ADD targetInnerType VARCHAR(64) NULL;");
            $this->getConnection()->exec("ALTER TABLE referer_log MODIFY COLUMN targetInnerType VARCHAR(64) COMMENT '模块自身的类型';");
        }

        if (!$this->isFieldExist('referer_log', 'token')) {
            $this->getConnection()->exec("ALTER TABLE referer_log ADD token VARCHAR(64) DEFAULT NULL  COMMENT '当前访问的token值';");
        }

        if (!$this->isFieldExist('referer_log', 'ip')) {
            $this->getConnection()->exec("ALTER TABLE referer_log ADD ip VARCHAR(64) DEFAULT NULL  COMMENT '访问者IP';");
        }

        if (!$this->isFieldExist('referer_log', 'targetInnerType')) {
            $this->getConnection()->exec("ALTER TABLE referer_log ADD targetInnerType VARCHAR(64) NULL;");
        }

        if ($this->isFieldExist('referer_log', 'targetInnerType')) {
            $this->getConnection()->exec("ALTER TABLE referer_log MODIFY COLUMN targetInnerType VARCHAR(64) COMMENT '模块自身的类型';");
        }

        if (!$this->isFieldExist('referer_log', 'userAgent')) {
            $this->getConnection()->exec("ALTER TABLE referer_log ADD userAgent text COMMENT '浏览器的标识';");
        }

        if ($this->isFieldExist('referer_log', 'refererUrl')) {
            $this->getConnection()->exec("ALTER TABLE referer_log MODIFY COLUMN refererUrl VARCHAR(1024) DEFAULT '' COMMENT '访问来源Url';");
        }

        if ($this->isFieldExist('referer_log', 'refererHost')) {
            $this->getConnection()->exec("ALTER TABLE referer_log MODIFY COLUMN refererHost VARCHAR(1024) DEFAULT '' COMMENT '访问来源Url';");
        }

        if (!$this->isFieldExist('referer_log', 'uri')) {
            $this->getConnection()->exec("ALTER TABLE referer_log ADD uri VARCHAR(1024) DEFAULT '' COMMENT '访问Url'");
        }

        if ($this->isFieldExist('referer_log', 'token')) {
            $this->getConnection()->exec("ALTER TABLE `referer_log` DROP `token`;");
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql    = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isblockTemplateEmpty()
    {
        $sql    = "select * from block_template";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? true : false;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}
