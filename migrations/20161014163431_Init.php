<?php

use Phpmig\Migration\Migration;

class Init extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `article` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
              `title` varchar(255) NOT NULL COMMENT '文章标题',
              `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目',
              `tagIds` tinytext COMMENT 'tag标签',
              `source` varchar(1024) DEFAULT '' COMMENT '来源',
              `sourceUrl` varchar(1024) DEFAULT '' COMMENT '来源URL',
              `publishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
              `body` text COMMENT '正文',
              `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
              `originalThumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图原图',
              `picture` varchar(255) NOT NULL DEFAULT '' COMMENT '文章头图，文章编辑／添加时，自动取正文的第１张图',
              `status` enum('published','unpublished','trash') NOT NULL DEFAULT 'unpublished' COMMENT '状态',
              `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击量',
              `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否头条',
              `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '推荐',
              `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
              `postNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复数',
              `upsNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章发布人的ID',
              `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
              `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              PRIMARY KEY (`id`),
              KEY `updatedTime` (`updatedTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `article_category` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL COMMENT '栏目名称',
              `code` varchar(64) NOT NULL COMMENT 'URL目录名称',
              `weight` int(11) NOT NULL DEFAULT '0',
              `publishArticle` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许发布文章',
              `seoTitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '栏目标题',
              `seoKeyword` varchar(1024) NOT NULL DEFAULT '' COMMENT 'SEO 关键字',
              `seoDesc` varchar(1024) NOT NULL DEFAULT '' COMMENT '栏目描述（SEO）',
              `published` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用（1：启用 0：停用)',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `block` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(11) NOT NULL COMMENT '用户Id',
              `blockTemplateId` INT(11) NOT NULL COMMENT '模版ID',
              `orgId` INT(11) NOT NULL DEFAULT 1 COMMENT '组织机构Id',
              `content` text COMMENT '编辑区的内容',
              `code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '编辑区编码',
              `meta` text COMMENT '编辑区元信息',
              `data` text COMMENT '编辑区内容',
              `createdTime` int(11) unsigned NOT NULL,
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `block_code_orgId_index` (`code`,`orgId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `block_template` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '模版ID',
              `title` varchar(255) NOT NULL COMMENT '标题',
              `mode` ENUM('html','template') NOT NULL DEFAULT 'html' COMMENT '模式' ,
              `template` text COMMENT '模板',
              `templateName` VARCHAR(255)  COMMENT '编辑区模板名字',
              `templateData` text  COMMENT '模板数据',
              `content` text COMMENT '默认内容',
              `data` text COMMENT '编辑区内容',
              `code` varchar(255) NOT NULL DEFAULT '' COMMENT '编辑区编码',
              `meta` text  COMMENT '编辑区元信息',
              `tips` VARCHAR( 255 ),
              `category` varchar(60) NOT NULL DEFAULT 'system' COMMENT '分类(系统/主题)',
              `createdTime` int(11) UNSIGNED NOT NULL COMMENT '创建时间',
              `updateTime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`)                  
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='编辑区模板';

            CREATE TABLE IF NOT EXISTS `block_history` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
              `blockId` int(11) NOT NULL COMMENT 'blockId',
              `templateData` text COMMENT '模板历史数据',
              `data` text COMMENT 'block数据',
              `content` text COMMENT 'content',
              `userId` int(11) NOT NULL COMMENT 'userId',
              `createdTime` int(11) unsigned NOT NULL COMMENT 'createdTime',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='历史表';

            CREATE TABLE IF NOT EXISTS `cache` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '缓存ID',
              `name` varchar(128) NOT NULL DEFAULT '' COMMENT '缓存名称',
              `data` longblob COMMENT '缓存数据',
              `serialized` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存是否为序列化的标记位',
              `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '缓存过期时间',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '缓存创建时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`),
              KEY `expiredTime` (`expiredTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `category` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
              `code` varchar(64) NOT NULL DEFAULT '' COMMENT '分类编码',
              `name` varchar(255) NOT NULL COMMENT '分类名称',
              `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
              `path` varchar(255) NOT NULL DEFAULT '' COMMENT '分类完整路径',
              `weight` int(11) NOT NULL DEFAULT '0' COMMENT '分类权重',
              `groupId` int(10) unsigned NOT NULL COMMENT '分类组ID',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父分类ID',
              `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
              `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
              `description` text,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uri` (`code`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `category_group` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类组ID',
              `code` varchar(64) NOT NULL COMMENT '分类组编码',
              `name` varchar(255) NOT NULL COMMENT '分类组名称',
              `depth` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该组下分类允许的最大层级数',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `cloud_app` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '云应用ID',
              `name` varchar(255) NOT NULL COMMENT '云应用名称',
              `code` varchar(64) NOT NULL COMMENT '云应用编码',
              `type` enum('plugin','theme') NOT NULL DEFAULT 'plugin' COMMENT '应用类型(plugin插件应用, theme主题应用)',
              `description` text NOT NULL COMMENT '云应用描述',
              `icon` varchar(255) NOT NULL COMMENT '云应用图标',
              `version` varchar(32) NOT NULL COMMENT '云应用当前版本',
              `fromVersion` varchar(32) NOT NULL DEFAULT '0.0.0' COMMENT '云应用更新前版本',
              `developerId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '云应用开发者用户ID',
              `developerName` varchar(255) NOT NULL DEFAULT '' COMMENT '云应用开发者名称',
              `edusohoMinVersion`  VARCHAR(32) NOT NULL DEFAULT '0.0.0' COMMENT '依赖Edusoho的最小版本',
              `edusohoMaxVersion`  VARCHAR(32) NOT NULL DEFAULT 'up' COMMENT '依赖Edusoho的最大版本',
              `installedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '云应用安装时间',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '云应用最后更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='已安装的应用';

            CREATE TABLE IF NOT EXISTS `cloud_app_logs` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '云应用运行日志ID',
              `code` varchar(32) NOT NULL DEFAULT '' COMMENT '应用编码',
              `name` varchar(32) NOT NULL DEFAULT '' COMMENT '应用名称',
              `fromVersion` varchar(32) DEFAULT NULL COMMENT '升级前版本',
              `toVersion` varchar(32) NOT NULL DEFAULT '' COMMENT '升级后版本',
              `type` enum('install','upgrade') NOT NULL DEFAULT 'install' COMMENT '升级类型',
              `dbBackupPath` varchar(255) NOT NULL DEFAULT '' COMMENT '数据库备份文件',
              `sourceBackupPath` varchar(255) NOT NULL DEFAULT '' COMMENT '源文件备份地址',
              `status` varchar(32) NOT NULL COMMENT '升级状态(ROLLBACK,ERROR,SUCCESS,RECOVERED)',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
              `ip` varchar(32) NOT NULL DEFAULT '' COMMENT 'IP',
              `message` text COMMENT '失败原因',
              `createdTime` int(10) unsigned NOT NULL COMMENT '日志记录时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='应用升级日志';

            CREATE TABLE IF NOT EXISTS `comment` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `objectType` varchar(32) NOT NULL,
              `objectId` int(10) unsigned NOT NULL,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `content` text NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `objectType` (`objectType`,`objectId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `content` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '内容ID',
              `title` varchar(255) NOT NULL COMMENT '内容标题',
              `editor` enum('richeditor','none') NOT NULL DEFAULT 'richeditor' COMMENT '编辑器选择类型字段',
              `type` varchar(255) NOT NULL COMMENT '内容类型',
              `alias` varchar(255) NOT NULL DEFAULT '' COMMENT '内容别名',
              `summary` text COMMENT '内容摘要',
              `body` text COMMENT '内容正文',
              `picture` varchar(255) NOT NULL DEFAULT '' COMMENT '内容头图',
              `template` varchar(255) NOT NULL DEFAULT '' COMMENT '内容模板',
              `status` enum('published','unpublished','trash') NOT NULL COMMENT '内容状态',
              `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容分类ID',
              `tagIds` tinytext COMMENT '内容标签ID',
              `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容点击量',
              `featured` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否头条',
              `promoted` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否推荐',
              `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
              `userId` int(10) unsigned NOT NULL COMMENT '发布人ID',
              `field1` text COMMENT '扩展字段',
              `field2` text COMMENT '扩展字段',
              `field3` text COMMENT '扩展字段',
              `field4` text COMMENT '扩展字段',
              `field5` text COMMENT '扩展字段',
              `field6` text COMMENT '扩展字段',
              `field7` text COMMENT '扩展字段',
              `field8` text COMMENT '扩展字段',
              `field9` text COMMENT '扩展字段',
              `field10` text COMMENT '扩展字段',
              `publishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程ID',
              `title` varchar(1024) NOT NULL COMMENT '课程标题',
              `subtitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '课程副标题',
              `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft' COMMENT '课程状态',
              `type` varchar(255) NOT NULL DEFAULT 'normal' COMMENT '课程类型',
              `maxStudentNum` int(11) NOT NULL DEFAULT '0' COMMENT '直播课程最大学员数上线',
              `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程价格',
              `originPrice` FLOAT(10,2) NOT NULL DEFAULT  '0.00' COMMENT '课程人民币原价',
              `coinPrice` FLOAT(10,2) NOT NULL DEFAULT 0.00,
              `originCoinPrice` FLOAT(10,2) NOT NULL DEFAULT  0 COMMENT '课程虚拟币原价',
              `expiryDay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程过期天数',
              `showStudentNumType` enum('opened','closed') NOT NULL DEFAULT 'opened' COMMENT '学员数显示模式',
              `serializeMode` enum('none','serialize','finished') NOT NULL DEFAULT 'none' COMMENT '连载模式',
              `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程销售总收入',
              `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
              `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课程所有课时，可获得的总学分',
              `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排行分数',
              `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票人数',
              `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可以免费看的，会员等级',
              `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
              `tags` text COMMENT '标签IDs',
              `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
              `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
              `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
              `about` text COMMENT '简介',
              `teacherIds` text COMMENT '显示的课程教师IDs',
              `goals` text COMMENT '课程目标',
              `audiences` text COMMENT '适合人群',
              `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
              `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
              `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
              `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID',
              `address` varchar(255) NOT NULL DEFAULT '' COMMENT '上课地区地址',
              `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
              `userId` int(10) unsigned NOT NULL COMMENT '课程发布人ID',
              `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '折扣活动ID',
              `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣',
              `deadlineNotify` enum('active','none') NOT NULL DEFAULT 'none' COMMENT '开启有效期通知',
              `daysOfNotifyBeforeDeadline` INT(10) NOT NULL DEFAULT '0',
              `useInClassroom` ENUM('single','more') NOT NULL DEFAULT 'single' COMMENT '课程能否用于多个班级' , 
              `watchLimit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '视频观看次数限制',
              `singleBuy` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '加入班级后课程能否单独购买' ,
              `createdTime` int(10) unsigned NOT NULL COMMENT '课程创建时间',
              `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `freeStartTime` int(10) NOT NULL DEFAULT '0',
              `freeEndTime` int(10) NOT NULL DEFAULT '0',
              `approval` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要实名认证',
              `parentId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程的父Id',
              `noteNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程笔记数量',
              `locked` INT(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁',
              `maxRate` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
              `buyable` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否开放购买',
              `tryLookable` TINYINT NOT NULL DEFAULT '0',
              `tryLookTime` INT NOT NULL DEFAULT '0',
              `conversationId` varchar(255) NOT NULL DEFAULT '0',
              `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
              `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',

              PRIMARY KEY (`id`),
              KEY `updatedTime` (`updatedTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

            CREATE TABLE IF NOT EXISTS `announcement` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '课程公告ID',
              `userId` int(10) unsigned NOT NULL COMMENT '公告发布人ID',
              `targetType` varchar(64) NOT NULL DEFAULT 'course' COMMENT '公告类型',
              `url` varchar(255) NOT NULL,
              `startTime` int(10) unsigned NOT NULL DEFAULT '0',
              `endTime` int(10) unsigned NOT NULL DEFAULT '0',
              `targetId` INT(10) UNSIGNED NOT NULL COMMENT '所属ID',
              `content` text NOT NULL COMMENT '公告内容',
              `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
              `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
              `copyId` INT(11) NOT NULL DEFAULT '0' COMMENT '复制的公告ID',
              `createdTime` int(10) NOT NULL COMMENT '公告创建时间',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告最后更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_chapter` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程章节ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '章节所属课程ID',
              `type` enum('chapter','unit') NOT NULL DEFAULT 'chapter' COMMENT '章节类型：chapter为章节，unit为单元。',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'parentId大于０时为单元',
              `number` int(10) unsigned NOT NULL COMMENT '章节编号',
              `seq` int(10) unsigned NOT NULL COMMENT '章节序号',
              `title` varchar(255) NOT NULL COMMENT '章节名称',
              `createdTime` int(10) unsigned NOT NULL COMMENT '章节创建时间',
              `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制章节的id',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_draft` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL COMMENT '标题',
              `summary` text COMMENT '摘要',
              `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `content` text COMMENT '内容',
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `lessonId` int(10) unsigned NOT NULL COMMENT '课时ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `course_favorite` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '收藏ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '收藏课程的ID',
              `userId` int(10) unsigned NOT NULL COMMENT '收藏人的ID',
              `createdTime` int(10) NOT NULL COMMENT '创建时间',
              `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
              PRIMARY KEY (`id`),
              KEY `course_favorite_userId_courseId_type_index` (`userId`,`courseId`,`type`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户的收藏数据表';

            CREATE TABLE IF NOT EXISTS `course_lesson` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课时ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '课时所属课程ID',
              `chapterId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时所属章节ID',
              `number` int(10) unsigned NOT NULL COMMENT '课时编号',
              `seq` int(10) unsigned NOT NULL COMMENT '课时在课程中的序号',
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
              `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated',
              `maxOnlineNum` INT NULL DEFAULT '0' COMMENT '直播在线人数峰值',
              `liveProvider` int(10) unsigned NOT NULL DEFAULT '0',
              `userId` int(10) unsigned NOT NULL COMMENT '发布人ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id',
              `suggestHours` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '建议学习时长',
              `testMode` ENUM('normal', 'realTime') NULL DEFAULT 'normal' COMMENT '考试模式',
              `testStartTime` INT(10) NULL DEFAULT '0' COMMENT '实时考试开始时间',
              PRIMARY KEY (`id`),
              KEY `updatedTime` (`updatedTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_lesson_learn` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '学员课时学习记录ID',
              `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `lessonId` int(10) unsigned NOT NULL COMMENT '课时ID',
              `status` enum('learning','finished') NOT NULL COMMENT '学习状态',
              `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习开始时间',
              `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习完成时间',
              `learnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习时间',
              `watchTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习观看时间',
              `watchNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '观看次数',
              `videoStatus` enum('paused','playing') NOT NULL DEFAULT 'paused' COMMENT '学习观看时间',
              `updateTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `userId_lessonId` (`userId`,`lessonId`),
              KEY `userId_courseId` (`userId`,`courseId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS  `course_lesson_replay` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `lessonId` int(10) unsigned NOT NULL COMMENT '所属课时',
              `courseId` int(10) unsigned NOT NULL COMMENT '所属课程',
              `title` varchar(255) NOT NULL COMMENT '名称',
              `replayId` text NOT NULL COMMENT '云直播中的回放id',
              `userId` int(10) unsigned NOT NULL COMMENT '创建者',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `hidden` tinyint(1) unsigned DEFAULT '0' COMMENT '观看状态',
              `type` varchar(50) NOT NULL DEFAULT 'live' COMMENT '课程类型',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `course_lesson_view` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `courseId` int(10) NOT NULL,
              `lessonId` int(10) NOT NULL,
              `fileId` int(10) NOT NULL,
              `userId` int(10) NOT NULL,
              `fileType` enum('document','video','audio','image','ppt','other','none') NOT NULL DEFAULT 'none',
              `fileStorage` enum('local','cloud','net','none') NOT NULL DEFAULT 'none',
              `fileSource` varchar(32) NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_material` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程资料ID',
              `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课程ID',
              `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课时ID',
              `title` varchar(1024) NOT NULL COMMENT '资料标题',
              `description` text COMMENT '资料描述',
              `link` varchar(1024) NOT NULL DEFAULT '' COMMENT '外部链接地址',
              `fileId` int(10) unsigned NOT NULL COMMENT '资料文件ID',
              `fileUri` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件URI',
              `fileMime` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件MIME',
              `fileSize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小',
              `source` varchar(50) NOT NULL DEFAULT 'coursematerial',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料创建人ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '资料创建时间',
              `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id',
              `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_member` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程学员记录ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `classroomId` INT(10) NOT NULL DEFAULT '0'  COMMENT '班级ID',
              `joinedType` ENUM('course','classroom') NOT NULL DEFAULT 'course' COMMENT '购买班级或者课程加入学习',
              `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
              `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员购买课程时的订单ID',
              `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习最后期限',
              `levelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户以会员的方式加入课程学员时的会员ID',
              `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学课时数',
              `credit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员已获得的学分',
              `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记数目',
              `noteLastUpdateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新的笔记更新时间',
              `isLearned` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已学完',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
              `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
              `isVisible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '可见与否，默认为可见',
              `role` enum('student','teacher') NOT NULL DEFAULT 'student' COMMENT '课程会员角色',
              `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '学员是否被锁定',
              `deadlineNotified` int(10) NOT NULL DEFAULT '0' COMMENT '有效期通知',
              `createdTime` int(10) unsigned NOT NULL COMMENT '学员加入课程时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `courseId` (`courseId`,`userId`),
              KEY `courseId_role_createdTime` (`courseId`,`role`,`createdTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_note` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '笔记ID',
              `userId` int(10) NOT NULL COMMENT '笔记作者ID',
              `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程ID',
              `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时ID',
              `content` text NOT NULL COMMENT '笔记内容',
              `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记内容的字数',
              `likeNum` INT(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞人数',
              `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '笔记状态：0:私有, 1:公开',
              `createdTime` int(10) NOT NULL COMMENT '笔记创建时间',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_note_like` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `noteId` int(11) NOT NULL,
              `userId` int(11) NOT NULL,
              `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_review` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程评价ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评价人ID',
              `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评价的课程ID',
              `title` varchar(255) NOT NULL DEFAULT '' COMMENT '评价标题',
              `content` text NOT NULL COMMENT '评论内容',
              `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分',
              `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
              `createdTime` int(10) unsigned NOT NULL COMMENT '评价创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_thread` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程话题ID',
              `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题所属课程ID',
              `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题所属课时ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题发布人ID',
              `type` enum('discussion','question') NOT NULL DEFAULT 'discussion' COMMENT '话题类型',
              `isStick` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
              `isElite` tinyint(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否精华',
              `isClosed` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否关闭',
              `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
              `title` varchar(255) NOT NULL COMMENT '话题标题',
              `content` text COMMENT '话题内容',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看数',
              `followNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关注数',
              `latestPostUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人ID',
              `latestPostTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题创建时间',
              `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              PRIMARY KEY (`id`),
              KEY `updatedTime` (`updatedTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            

            CREATE TABLE IF NOT EXISTS `course_thread_post` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程话题回复ID',
              `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复所属课程ID',
              `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复所属课时ID',
              `threadId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复所属话题ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复人',
              `isElite` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否精华',
              `content` text NOT NULL COMMENT '正文',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `file` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '上传文件ID',
              `groupId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传文件组ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传人ID',
              `uri` varchar(255) NOT NULL COMMENT '文件URI',
              `mime` varchar(255) NOT NULL COMMENT '文件MIME',
              `size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
              `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '文件状态',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件上传时间',
              `uploadFileId` INT(10) NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `file_group` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '上传文件组ID',
              `name` varchar(255) NOT NULL COMMENT '上传文件组名称',
              `code` varchar(255) NOT NULL COMMENT '上传文件组编码',
              `public` tinyint(4) NOT NULL DEFAULT '1' COMMENT '文件组文件是否公开',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `friend` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '关注ID',
              `fromId` int(10) unsigned NOT NULL COMMENT '关注人ID',
              `toId` int(10) unsigned NOT NULL COMMENT '被关注人ID',
              `pair` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否为互加好友',
              `createdTime` int(10) unsigned NOT NULL COMMENT '关注时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS  `groups` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '小组id',
              `title` varchar(100) NOT NULL COMMENT '小组名称',
              `about` text COMMENT '小组介绍',
              `logo` varchar(100) NOT NULL DEFAULT '' COMMENT 'logo',
              `backgroundLogo` varchar(100) NOT NULL DEFAULT '',
              `status` enum('open','close') NOT NULL DEFAULT 'open',
              `memberNum` int(10) unsigned NOT NULL DEFAULT '0',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `ownerId` int(10) unsigned NOT NULL COMMENT '小组组长id',
              `createdTime` int(11) unsigned NOT NULL COMMENT '创建小组时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `groups_member` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '成员id主键',
              `groupId` int(10) unsigned NOT NULL COMMENT '小组id',
              `userId` int(10) unsigned NOT NULL COMMENT '用户id',
              `role` varchar(100) NOT NULL DEFAULT 'member',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(11) unsigned NOT NULL COMMENT '加入时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `groups_thread` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '话题id',
              `title` varchar(1024) NOT NULL COMMENT '话题标题',
              `content` text COMMENT '话题内容',
              `isElite` int(11) unsigned NOT NULL DEFAULT '0',
              `isStick` int(11) unsigned NOT NULL DEFAULT '0',
              `lastPostMemberId` int(10) unsigned NOT NULL DEFAULT '0',
              `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0',
              `groupId` int(10) unsigned NOT NULL,
              `userId` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL COMMENT '添加时间',
              `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `status` enum('open','close') NOT NULL DEFAULT 'open',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
              `rewardCoin` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
              `type` VARCHAR(255) NOT NULL DEFAULT 'default',
              PRIMARY KEY (`id`),
              KEY `updatedTime` (`updatedTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `groups_thread_post` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
              `threadId` int(11) unsigned NOT NULL COMMENT '话题id',
              `content` text NOT NULL COMMENT '回复内容',
              `userId` int(10) unsigned NOT NULL COMMENT '回复人id',
              `fromUserId` int(10) unsigned NOT NULL DEFAULT '0',
              `postId` int(10) unsigned DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL COMMENT '回复时间',
              `adopt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `location` (
              `id` bigint(20) unsigned NOT NULL,
              `parentId` bigint(20) NOT NULL DEFAULT '0',
              `name` varchar(255) NOT NULL,
              `pinyin` varchar(255) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统日志ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
              `module` varchar(32) NOT NULL COMMENT '日志所属模块',
              `action` varchar(32) NOT NULL COMMENT '日志所属操作类型',
              `message` text NOT NULL COMMENT '日志内容',
              `data` text COMMENT '日志数据',
              `ip` varchar(255) NOT NULL COMMENT '日志记录IP',
              `createdTime` int(10) unsigned NOT NULL COMMENT '日志发生时间',
              `level` char(10) NOT NULL COMMENT '日志等级',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `message` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信Id',
              `type` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '私信类型',
              `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
              `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
              `content` text NOT NULL COMMENT '私信内容',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '私信发送时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `message_conversation` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会话Id',
              `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
              `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
              `messageNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '此对话的信息条数',
              `latestMessageUserId` int(10) unsigned DEFAULT NULL COMMENT '最后发信人ID',
              `latestMessageTime` int(10) unsigned NOT NULL COMMENT '最后发信时间',
              `latestMessageContent` text NOT NULL COMMENT '最后发信内容',
              `latestMessageType` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '最后一条私信类型',
              `unreadNum` int(10) unsigned NOT NULL COMMENT '未读数量',
              `createdTime` int(10) unsigned NOT NULL COMMENT '会话创建时间',
              PRIMARY KEY (`id`),
              KEY `toId_fromId` (`toId`,`fromId`),
              KEY `toId_latestMessageTime` (`toId`,`latestMessageTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `message_relation` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '消息关联ID',
              `conversationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的会话ID',
              `messageId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的消息ID',
              `isRead` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否已读',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `migration_versions` (
              `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

            CREATE TABLE IF NOT EXISTS `mobile_device` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '设备ID',
              `imei` varchar(255) NOT NULL COMMENT '串号',
              `platform` varchar(255) NOT NULL COMMENT '平台',
              `version` varchar(255) NOT NULL COMMENT '版本',
              `screenresolution` varchar(100) NOT NULL COMMENT '分辨率',
              `kernel` varchar(255) NOT NULL COMMENT '内核',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `navigation` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '导航ID',
              `name` varchar(255) NOT NULL COMMENT '导航名称',
              `url` varchar(300) NOT NULL COMMENT '链接地址',
              `sequence` tinyint(4) unsigned NOT NULL COMMENT '显示顺序',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父导航ID',
              `createdTime` int(11) NOT NULL COMMENT '创建时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `type` varchar(30) NOT NULL COMMENT '类型',
              `isOpen` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认1，为开启',
              `isNewWin` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认为1,另开窗口',
              `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
              `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',

              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='导航数据表';

            CREATE TABLE IF NOT EXISTS `notification` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '通知ID',
              `userId` int(10) unsigned NOT NULL COMMENT '被通知的用户ID',
              `type` varchar(64) NOT NULL DEFAULT 'default' COMMENT '通知类型',
              `content` text COMMENT '通知内容',
              `batchId` int(10) NOT NULL DEFAULT '0' COMMENT '群发通知表中的ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '通知时间',
              `isRead` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `orders` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
              `sn` varchar(32) NOT NULL COMMENT '订单编号',
              `status` enum('created','paid','refunding','refunded','cancelled') NOT NULL COMMENT '订单状态',
              `title` varchar(255) NOT NULL COMMENT '订单标题',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '订单所属对象类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单所属对象ID',
              `amount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单实付金额',
              `totalPrice` FLOAT(10,2) NOT NULL DEFAULT '0' COMMENT '订单总价',
              `isGift` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为赠送礼物',
              `giftTo` varchar(64) NOT NULL DEFAULT '' COMMENT '赠送给用户ID',
              `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '折扣活动ID',
              `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣',
              `token` VARCHAR(50) NULL DEFAULT NULL COMMENT '令牌',
              `refundId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次退款操作记录的ID',
              `userId` int(10) unsigned NOT NULL COMMENT '订单创建人',
              `coupon` varchar(255) NOT NULL DEFAULT '' COMMENT '优惠码',
              `couponDiscount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '优惠码扣减金额',
              `payment` varchar(32) NOT NULL DEFAULT 'none' COMMENT '订单支付方式',
              `coinAmount` FLOAT(10,2) NOT NULL DEFAULT '0' COMMENT '虚拟币支付额',
              `coinRate` FLOAT(10,2) NOT NULL DEFAULT '1' COMMENT '虚拟币汇率',
              `priceType` enum('RMB','Coin') NOT NULL DEFAULT 'RMB' COMMENT '创建订单时的标价类型',
              `bank` varchar(32) NOT NULL DEFAULT '' COMMENT '银行编号',
              `paidTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
              `cashSn` BIGINT(20) NULL COMMENT '支付流水号',
              `note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
              `data` text COMMENT '订单业务数据',
              `createdTime` int(10) unsigned NOT NULL COMMENT '订单创建时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `sn` (`sn`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `order_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单日志ID',
              `orderId` int(10) unsigned NOT NULL COMMENT '订单ID',
              `type` varchar(32) NOT NULL COMMENT '订单日志类型',
              `message` text COMMENT '订单日志内容',
              `data` text COMMENT '订单日志数据',
              `userId` int(10) unsigned NOT NULL COMMENT '订单操作人',
              `ip` varchar(255) NOT NULL COMMENT '订单操作IP',
              `createdTime` int(10) unsigned NOT NULL COMMENT '订单日志记录时间',
              PRIMARY KEY (`id`),
              KEY `orderId` (`orderId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `order_refund` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单退款记录ID',
              `orderId` int(10) unsigned NOT NULL COMMENT '退款订单ID',
              `userId` int(10) unsigned NOT NULL COMMENT '退款人ID',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '订单退款记录所属对象类型',
              `targetId` int(10) unsigned NOT NULL COMMENT '订单退款记录所属对象ID',
              `status` enum('created','success','failed','cancelled') NOT NULL DEFAULT 'created' COMMENT '退款状态',
              `expectedAmount` float(10,2) unsigned DEFAULT '0.00' COMMENT '期望退款的金额，NULL代表未知，0代表不需要退款',
              `actualAmount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际退款金额，0代表无退款',
              `reasonType` varchar(64) NOT NULL DEFAULT '' COMMENT '退款理由类型',
              `reasonNote` varchar(1024) NOT NULL DEFAULT '' COMMENT '退款理由',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单退款记录最后更新时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '订单退款记录创建时间',
              `operator` int(11) NOT NULL COMMENT '操作人',
              UNIQUE KEY `id` (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `question` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目ID',
              `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
              `stem` text COMMENT '题干',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
              `answer` text COMMENT '参考答案',
              `analysis` text COMMENT '解析',
              `metas` text COMMENT '题目元信息',
              `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类别',
              `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
              `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
              `parentId` int(10) unsigned DEFAULT '0' COMMENT '材料父ID',
              `subCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子题数量',
              `finishedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成次数',
              `passedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功次数',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制问题对应Id',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='问题表';

            CREATE TABLE IF NOT EXISTS `question_category` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目类别ID',
              `name` varchar(255) NOT NULL COMMENT '类别名称',
              `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库类别表';

            CREATE TABLE IF NOT EXISTS `question_favorite` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目收藏ID',
              `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被收藏的题目ID',
              `target` varchar(255) NOT NULL DEFAULT '' COMMENT '题目所属对象',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏人ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `sessions` (
              `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
              `sess_user_id` INT UNSIGNED NOT NULL DEFAULT  '0',
              `sess_data` BLOB NOT NULL,
              `sess_time` INTEGER UNSIGNED NOT NULL,
              `sess_lifetime` MEDIUMINT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `setting` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统设置ID',
              `name` varchar(64) NOT NULL DEFAULT '' COMMENT '系统设置名',
              `value` longblob COMMENT '系统设置值',
              `namespace` varchar(255) NOT NULL DEFAULT 'default',
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`, `namespace`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `shortcut` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL,
              `title` varchar(255) NOT NULL DEFAULT '',
              `url` varchar(255) NOT NULL DEFAULT '',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `status` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '动态发布的人',
              `courseId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程Id',
              `classroomId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班级id',
              `type` varchar(64) NOT NULL COMMENT '动态类型',
              `objectType` varchar(64) NOT NULL DEFAULT '' COMMENT '动态对象的类型',
              `objectId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态对象ID',
              `message` text NOT NULL COMMENT '动态的消息体',
              `properties` text NOT NULL COMMENT '动态的属性',
              `commentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
              `likeNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被赞的数量',
              `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态发布时间',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`),
              KEY `createdTime` (`createdTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `tag` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
              `name` varchar(64) NOT NULL COMMENT '标签名称',
              `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
              `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
              `createdTime` int(10) unsigned NOT NULL COMMENT '标签创建时间',

              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `testpaper` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷ID',
              `name` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
              `description` text COMMENT '试卷说明',
              `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：秒)',
              `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷生成/显示模式',
              `target` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷所属对象',
              `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状态：draft,open,closed',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
              `passedScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '通过考试的分数线',
              `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
              `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
              `metas` text COMMENT '题型排序',
              `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `testpaper_item` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷条目ID',
              `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
              `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目ID',
              `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父题ID',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
              `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '漏选得分',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `testpaper_item_result` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷题目做题结果ID',
              `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷条目ID',
              `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷ID',
              `testPaperResultId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷结果ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做题人ID',
              `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目ID',
              `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none' COMMENT '结果状态',
              `score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '得分',
              `answer` text COMMENT '回答',
              `teacherSay` text COMMENT '老师评价',
              `pId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id',
              PRIMARY KEY (`id`),
              KEY `testPaperResultId` (`testPaperResultId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `testpaper_result` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷结果ID',
              `paperName` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
              `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做卷人ID',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
              `objectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '主观题得分',
              `subjectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '客观题得分',
              `teacherSay` text COMMENT '老师评价',
              `rightItemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '正确题目数',
              `passedStatus` enum('none','excellent','good','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '考试通过状态，none表示该考试没有',
              `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷限制时间(秒)',
              `beginTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
              `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `status` enum('doing','paused','reviewing','finished') NOT NULL COMMENT '状态',
              `target` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷结果所属对象',
              `checkTeacherId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '批卷老师ID',
              `checkedTime` int(11) NOT NULL DEFAULT '0' COMMENT '批卷时间',
              `usedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `theme_config` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL DEFAULT '',
              `config` text,
              `confirmConfig` text,
              `allConfig` text,
              `updatedTime` int(11) NOT NULL DEFAULT '0',
              `createdTime` int(11) NOT NULL DEFAULT '0',
              `updatedUserId` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `upgrade_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `remoteId` int(11) NOT NULL COMMENT 'packageId',
              `installedId` int(11) DEFAULT NULL COMMENT '本地已安装id',
              `ename` varchar(32) NOT NULL COMMENT '名称',
              `cname` varchar(32) NOT NULL COMMENT '中文名称',
              `fromv` varchar(32) DEFAULT NULL COMMENT '初始版本',
              `tov` varchar(32) NOT NULL COMMENT '目标版本',
              `type` smallint(6) NOT NULL COMMENT '升级类型',
              `dbBackPath` text COMMENT '数据库备份文件',
              `srcBackPath` text COMMENT '源文件备份地址',
              `status` varchar(32) NOT NULL COMMENT '状态(ROLLBACK,ERROR,SUCCESS,RECOVERED)',
              `logtime` int(11) NOT NULL COMMENT '升级时间',
              `uid` int(10) unsigned NOT NULL COMMENT 'uid',
              `ip` varchar(32) DEFAULT NULL COMMENT 'ip',
              `reason` text COMMENT '失败原因',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='本地升级日志表';

            CREATE TABLE IF NOT EXISTS `upload_files` (
              `id` int(10) unsigned NOT NULL COMMENT '上传文件ID',
              `globalId` VARCHAR(32) NOT NULL DEFAULT '0' COMMENT '云文件ID',
              `status` ENUM('uploading','ok') NOT NULL DEFAULT 'ok' COMMENT '文件上传状态',
              `hashId` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
              `targetId` int(11) NOT NULL COMMENT '所存目标ID',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型',
              `useType` varchar(64) DEFAULT NULL COMMENT '文件使用的模块类型' ,
              `filename` varchar(1024) NOT NULL DEFAULT '' COMMENT '文件名',
              `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
              `fileSize` bigint(20) NOT NULL DEFAULT '0' COMMENT '文件大小',
              `etag` varchar(256) NOT NULL DEFAULT '' COMMENT 'ETAG',
              `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '长度（音视频则为时长，PPT/文档为页数）',
              `description` text DEFAULT NUll,
              `convertHash` varchar(128) NOT NULL DEFAULT '' COMMENT '文件转换时的查询转换进度用的Hash值',
              `convertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none' COMMENT '文件转换状态',
              `convertParams` text COMMENT '文件转换参数',
              `metas` text COMMENT '元信息',
              `metas2` text COMMENT '元信息',
              `type` enum('document','video','audio','image','ppt','other','flash') NOT NULL DEFAULT 'other' COMMENT '文件类型',
              `storage` enum('local','cloud') NOT NULL COMMENT '文件存储方式',
              `isPublic` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否公开文件',
              `canDownload` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否可下载',
              `usedCount` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户名',
              `updatedTime` int(10) unsigned DEFAULT '0' COMMENT '文件最后更新时间',
              `createdUserId` int(10) unsigned NOT NULL COMMENT '文件上传人',
              `createdTime` int(10) unsigned NOT NULL COMMENT '文件上传时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `convertHash` (`convertHash`(64)),
              UNIQUE KEY `hashId` (`hashId`(120))
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `upload_file_inits` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `globalId` varchar(32) NOT NULL DEFAULT '0' COMMENT '云文件ID',
              `status` ENUM('uploading','ok') NOT NULL DEFAULT 'ok' COMMENT '文件上传状态',
              `hashId` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
              `targetId` int(11) NOT NULL COMMENT '所存目标id',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型',
              `filename` varchar(1024) NOT NULL DEFAULT '',
              `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
              `fileSize` bigint(20) NOT NULL DEFAULT '0',
              `etag` VARCHAR( 256 ) NOT NULL DEFAULT  '',
              `length` INT UNSIGNED NOT NULL DEFAULT  '0',
              `convertHash` varchar(256) NOT NULL DEFAULT '' COMMENT '文件转换时的查询转换进度用的Hash值',
              `convertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none',
              `metas` text,
              `metas2` TEXT NULL DEFAULT NULL,
              `type` ENUM(  'document',  'video',  'audio',  'image',  'ppt',  'flash', 'other' ) NOT NULL DEFAULT 'other',
              `storage` enum('local','cloud') NOT NULL,
              `convertParams` TEXT NULL COMMENT  '文件转换参数',
              `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户名',
              `updatedTime` int(10) unsigned DEFAULT '0',
              `createdUserId` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `hashId` (`hashId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `user` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
              `email` varchar(128) NOT NULL COMMENT '用户邮箱',
              `verifiedMobile` varchar(32) NOT NULL DEFAULT  '',
              `password` varchar(64) NOT NULL COMMENT '用户密码',
              `salt` varchar(32) NOT NULL COMMENT '密码SALT',
              `payPassword` varchar(64) NOT NULL DEFAULT '' COMMENT '支付密码',
              `payPasswordSalt` varchar(64) NOT NULL DEFAULT '' COMMENT '支付密码Salt',
              `locale` VARCHAR(20),
              `uri` varchar(64) NOT NULL DEFAULT '' COMMENT '用户URI',
              `nickname` varchar(64) NOT NULL COMMENT '用户名',
              `title` varchar(255) NOT NULL DEFAULT '' COMMENT '头衔',
              `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
              `type` varchar(32) NOT NULL COMMENT 'default默认为网站注册, weibo新浪微薄登录',
              `point` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
              `coin` int(11) NOT NULL DEFAULT '0' COMMENT '金币',
              `smallAvatar` varchar(255) NOT NULL DEFAULT '' COMMENT '小头像',
              `mediumAvatar` varchar(255) NOT NULL DEFAULT '' COMMENT '中头像',
              `largeAvatar` varchar(255) NOT NULL DEFAULT '' COMMENT '大头像',
              `emailVerified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '邮箱是否为已验证',
              `setup` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否初始化设置的，未初始化的可以设置邮箱、用户名。',
              `roles` varchar(255) NOT NULL COMMENT '用户角色',
              `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐',
              `promotedSeq` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `promotedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
              `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否被禁止',
              `lockDeadline` int(10) not null default '0' COMMENT '帐号锁定期限', 
              `consecutivePasswordErrorTimes` int not null default '0' COMMENT '帐号密码错误次数', 
              `lastPasswordFailTime` int(10) not null default '0',
              `loginTime` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
              `loginIp` varchar(64) NOT NULL DEFAULT '' COMMENT '最后登录IP',
              `loginSessionId` varchar(255) NOT NULL DEFAULT '' COMMENT '最后登录会话ID',
              `approvalTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '实名认证时间',
              `approvalStatus` enum('unapprove','approving','approved','approve_fail') NOT NULL DEFAULT 'unapprove' COMMENT '实名认证状态',
              `newMessageNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未读私信数',
              `newNotificationNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未读消息数',
              `createdIp` varchar(64) NOT NULL DEFAULT '' COMMENT '注册IP',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
              `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `inviteCode` varchar(255) NUll DEFAULT NUll COMMENT '邀请码',
              `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
              `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',

              PRIMARY KEY (`id`),
              UNIQUE KEY `email` (`email`),
              UNIQUE KEY `nickname` (`nickname`),
              KEY `updatedTime` (`updatedTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

            CREATE TABLE IF NOT EXISTS `user_approval` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '用户认证ID',
              `userId` int(10) NOT NULL COMMENT '用户ID',
              `idcard` varchar(24) NOT NULL DEFAULT '' COMMENT '身份证号',
              `faceImg` varchar(500) NOT NULL DEFAULT '' COMMENT '认证正面图',
              `backImg` varchar(500) NOT NULL DEFAULT '' COMMENT '认证背面图',
              `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
              `note` text COMMENT '认证信息',
              `status` enum('unapprove','approving','approved','approve_fail') NOT NULL COMMENT '是否通过：1是 0否',
              `operatorId` int(10) unsigned DEFAULT NULL COMMENT '审核人',
              `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '申请时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户认证表';

            CREATE TABLE IF NOT EXISTS `user_bind` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户绑定ID',
              `type` varchar(64) NOT NULL COMMENT '用户绑定类型',
              `fromId` varchar(32) NOT NULL COMMENT '来源方用户ID',
              `toId` int(10) unsigned NOT NULL COMMENT '被绑定的用户ID',
              `token` varchar(255) NOT NULL DEFAULT '' COMMENT 'oauth token',
              `refreshToken` varchar(255) NOT NULL DEFAULT '' COMMENT 'oauth refresh token',
              `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'token过期时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '绑定时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `type` (`type`,`fromId`),
              UNIQUE KEY `type_2` (`type`,`toId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `user_field` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `fieldName` varchar(100) NOT NULL DEFAULT '',
              `title` varchar(1024) NOT NULL DEFAULT '',
              `seq` int(10) unsigned NOT NULL,
              `enabled` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(100) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `user_fortune_log` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `userId` int(11) NOT NULL,
              `number` int(10) NOT NULL,
              `action` varchar(20) NOT NULL,
              `note` varchar(255) NOT NULL DEFAULT '',
              `createdTime` int(11) NOT NULL,
              `type` varchar(20) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `user_profile` (
              `id` int(10) unsigned NOT NULL COMMENT '用户ID',
              `truename` varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
              `idcard` varchar(24) NOT NULL DEFAULT '' COMMENT '身份证号码',
              `gender` enum('male','female','secret') NOT NULL DEFAULT 'secret' COMMENT '性别',
              `iam` varchar(255) NOT NULL DEFAULT '' COMMENT '我是谁',
              `birthday` date DEFAULT NULL COMMENT '生日',
              `city` varchar(64) NOT NULL DEFAULT '' COMMENT '城市',
              `mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '手机',
              `qq` varchar(32) NOT NULL DEFAULT '' COMMENT 'QQ',
              `signature` text COMMENT '签名',
              `about` text COMMENT '自我介绍',
              `company` varchar(255) NOT NULL DEFAULT '' COMMENT '公司',
              `job` varchar(255) NOT NULL DEFAULT '' COMMENT '工作',
              `school` varchar(255) NOT NULL DEFAULT '' COMMENT '学校',
              `class` varchar(255) NOT NULL DEFAULT '' COMMENT '班级',
              `weibo` varchar(255) NOT NULL DEFAULT '' COMMENT '微博',
              `weixin` varchar(255) NOT NULL DEFAULT '' COMMENT '微信',
              `isQQPublic` INT NOT NULL DEFAULT '0' COMMENT 'QQ号是否公开',
              `isWeixinPublic` INT NOT NULL DEFAULT '0' COMMENT '微信是否公开',
              `isWeiboPublic` INT NOT NULL DEFAULT '0' COMMENT '微博是否公开',
              `site` varchar(255) NOT NULL DEFAULT '' COMMENT '网站',
              `intField1` int(11) DEFAULT NULL,
              `intField2` int(11) DEFAULT NULL,
              `intField3` int(11) DEFAULT NULL,
              `intField4` int(11) DEFAULT NULL,
              `intField5` int(11) DEFAULT NULL,
              `dateField1` date DEFAULT NULL,
              `dateField2` date DEFAULT NULL,
              `dateField3` date DEFAULT NULL,
              `dateField4` date DEFAULT NULL,
              `dateField5` date DEFAULT NULL,
              `floatField1` float(10,2) DEFAULT NULL,
              `floatField2` float(10,2) DEFAULT NULL,
              `floatField3` float(10,2) DEFAULT NULL,
              `floatField4` float(10,2) DEFAULT NULL,
              `floatField5` float(10,2) DEFAULT NULL,
              `varcharField1` varchar(1024) DEFAULT NULL,
              `varcharField2` varchar(1024) DEFAULT NULL,
              `varcharField3` varchar(1024) DEFAULT NULL,
              `varcharField4` varchar(1024) DEFAULT NULL,
              `varcharField5` varchar(1024) DEFAULT NULL,
              `varcharField6` varchar(1024) DEFAULT NULL,
              `varcharField7` varchar(1024) DEFAULT NULL,
              `varcharField8` varchar(1024) DEFAULT NULL,
              `varcharField9` varchar(1024) DEFAULT NULL,
              `varcharField10` varchar(1024) DEFAULT NULL,
              `textField1` text,
              `textField2` text,
              `textField3` text,
              `textField4` text,
              `textField5` text,
              `textField6` text,
              `textField7` text,
              `textField8` text,
              `textField9` text,
              `textField10` text,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `user_token` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'TOKEN编号',
              `token` varchar(64) NOT NULL COMMENT 'TOKEN值',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKEN关联的用户ID',
              `type` varchar(255) NOT NULL COMMENT 'TOKEN类型',
              `data` text NOT NULL COMMENT 'TOKEN数据',
              `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKEN的校验次数限制(0表示不限制)',
              `remainedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKE剩余校验次数',
              `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKEN过期时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT 'TOKEN创建时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `token` (`token`(60))
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `cash_orders_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `orderId` int(10) unsigned NOT NULL,
              `message` text,
              `data` text,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `ip` varchar(255) NOT NULL,
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `type` varchar(255) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `cash_orders` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `sn` varchar(32) NOT NULL COMMENT '订单号',
              `status` enum('created','paid','cancelled') NOT NULL,
              `title` varchar(255) NOT NULL,
              `amount` float(10,2) unsigned NOT NULL DEFAULT '0.00',
              `payment` VARCHAR(32) NOT NULL DEFAULT 'none',
              `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
              `note` varchar(255) NOT NULL DEFAULT '',
              `targetType` VARCHAR(64) NOT NULL DEFAULT 'coin' COMMENT '订单类型',
              `token` VARCHAR(50) NULL DEFAULT NULL COMMENT '令牌',
              `data` TEXT NULL DEFAULT NULL COMMENT '订单业务数据',
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `cash_account` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL,
              `cash` float(10,2) NOT NULL DEFAULT '0.00',
              PRIMARY KEY (`id`),
              UNIQUE KEY `userId` (`userId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `cash_flow` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '帐号ID，即用户ID',
              `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
              `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
              `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
              `cashType` ENUM('RMB','Coin') NOT NULL DEFAULT 'Coin' COMMENT '账单类型',
              `cash` FLOAT(10,2) NOT NULL DEFAULT '0' COMMENT '账单生成后的余额',
              `parentSn` bigint(20) NULL COMMENT '上一个账单的流水号',
              `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
              `orderSn` varchar(40) NOT NULL COMMENT '订单号',
              `category` varchar(128) NOT NULL DEFAULT '' COMMENT '帐目类目',
              `payment` VARCHAR(32) NULL DEFAULT '',
              `note` text COMMENT '备注',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `tradeNo` (`sn`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='帐目流水' AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `groups_thread_collect` (
              `id` int(10) unsigned NOT NULL auto_increment COMMENT 'id主键',
              `threadId` int(11) unsigned NOT NULL COMMENT '收藏的话题id',
              `userId` int(10) unsigned NOT NULL COMMENT '收藏人id',
              `createdTime` int(10) unsigned NOT NULL COMMENT '收藏时间',
              PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `groups_thread_trade` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `threadId` int(10) unsigned DEFAULT '0',
              `goodsId` int(10) DEFAULT '0',
              `userId` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `groups_thread_goods` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` text NOT NULL,
              `description` text,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `type` enum('content','attachment','postAttachment') NOT NULL,
              `threadId` int(10) unsigned NOT NULL,
              `postId` int(10) unsigned NOT NULL DEFAULT '0',
              `coin` int(10) unsigned NOT NULL,
              `fileId` int(10) unsigned NOT NULL DEFAULT '0',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `upload_files_share` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `sourceUserId` int(10) UNSIGNED NOT NULL COMMENT '上传文件的用户ID',
              `targetUserId` int(10) UNSIGNED NOT NULL COMMENT '文件分享目标用户ID',
              `isActive` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否有效',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `user_secure_question` (
            `id` int(10) unsigned NOT NULL auto_increment ,
            `userId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
            `securityQuestionCode` varchar(64) NOT NULL DEFAULT '' COMMENT '问题的code',
            `securityAnswer` varchar(64) NOT NULL DEFAULT '' COMMENT '安全问题的答案',
            `securityAnswerSalt` varchar(64) NOT NULL DEFAULT '' COMMENT '安全问题的答案Salt',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',       
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `thread` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `targetType` varchar(255) NOT NULL DEFAULT 'classroom' COMMENT '所属 类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属类型 ID',
              `title` varchar(255) NOT NULL COMMENT '标题',
              `content` text COMMENT '内容',
              `ats` TEXT NULL DEFAULT NULL COMMENT  '@(提)到的人',
              `nice` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加精',
              `sticky` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
              `solved` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否有老师回答(已被解决)',
              `lastPostUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人ID',
              `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `type` varchar(255) NOT NULL DEFAULT '' COMMENT '话题类型',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
              `memberNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '成员人数',
              `status` enum('open','closed') NOT NULL DEFAULT 'open' COMMENT '状态',
              `startTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开始时间',
              `endTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结束时间',
              `maxUsers` INT(10) NOT NULL DEFAULT '0' COMMENT '最大人数',
              `actvityPicture` VARCHAR(255) NULL DEFAULT NULL COMMENT '活动图片',
              `location` VARCHAR(1024) DEFAULT NULL COMMENT '地点',
              `relationId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '从属ID' , 
              `categoryId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID' , 
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题最后一次被编辑或回复时间',
              PRIMARY KEY (`id`),
              KEY `updateTime` (`updateTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `thread_post` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `threadId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题ID',
              `content` text NOT NULL COMMENT '内容',
              `adopted` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否被采纳(是老师回答)',
              `ats` TEXT NULL DEFAULT NULL COMMENT  '@(提)到的人',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
              `subposts` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '子话题数量',
              `ups` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '投票数',
              `targetType` VARCHAR(255) NOT NULL DEFAULT 'classroom' COMMENT '所属 类型', 
              `targetId` INT(10) UNSIGNED NOT NULL COMMENT '所属 类型ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `sign_target_statistics` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
              `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
              `signedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到人数',
              `date` int(6) unsigned NOT NULL DEFAULT '0' COMMENT '统计日期',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

            CREATE TABLE IF NOT EXISTS `sign_user_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
              `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
              `rank` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到排名',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

            CREATE TABLE IF NOT EXISTS `sign_user_statistics` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
              `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
              `keepDays` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '连续签到天数',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

            CREATE TABLE IF NOT EXISTS `sign_card` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `cardNum` int(10) unsigned NOT NULL DEFAULT '0',
              `useTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `thread_vote` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `threadId` int(10) unsigned NOT NULL COMMENT '话题ID',
              `postId` int(10) unsigned NOT NULL COMMENT '回帖ID',
              `action` enum('up','down') NOT NULL COMMENT '投票类型',
              `userId` int(10) unsigned NOT NULL COMMENT '投票人ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '投票时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `postId` (`threadId`,`postId`,`userId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='话题投票表';

            CREATE TABLE IF NOT EXISTS `crontab_job` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `name` varchar(1024) NOT NULL COMMENT '任务名称',
              `cycle` ENUM('once','everyhour','everyday','everymonth') NOT NULL DEFAULT 'once' COMMENT '任务执行周期',
              `cycleTime` VARCHAR(255) NOT NULL DEFAULT '0' COMMENT '任务执行时间',
              `jobClass` varchar(1024) NOT NULL COMMENT '任务的Class名称',
              `jobParams` text NULL COMMENT '任务参数',
              `targetType` VARCHAR( 64 ) NOT NULL DEFAULT  '',
              `targetId` INT UNSIGNED NOT NULL DEFAULT  '0',
              `executing` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行状态',
              `nextExcutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务下次执行的时间',
              `latestExecutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务最后执行的时间',
              `creatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
              `createdTime` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `thread_member` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统Id',
              `threadId` int(10) unsigned NOT NULL COMMENT '话题Id',
              `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
              `nickname` varchar(255) DEFAULT NULL COMMENT '用户名',
              `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
              `mobile` varchar(32) DEFAULT NULL COMMENT '手机号码',
              `createdTIme` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='话题成员表';

            CREATE TABLE IF NOT EXISTS `classroom` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL COMMENT '标题',
              `status` enum('closed','draft','published') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布',
              `about` text COMMENT '简介',
              `categoryId` INT(10) NOT NULL DEFAULT '0' COMMENT '分类id',
              `description` text COMMENT '课程说明',
              `price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
              `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支持的vip等级',
              `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
              `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
              `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
              `headTeacherId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班主任ID',
              `teacherIds` varchar(255) NOT NULL DEFAULT '' COMMENT '教师IDs',
              `assistantIds` TEXT COMMENT '助教Ids',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
              `auditorNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '旁听生数',
              `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
              `courseNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程数',
              `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题数',
              `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级笔记数量',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
              `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '收入',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `service` varchar(255) DEFAULT NULL COMMENT '班级服务',
              `private` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否封闭班级',
              `recommended` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐班级',
              `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '推荐序号',
              `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
              `rating` FLOAT UNSIGNED NOT NULL DEFAULT '0' COMMENT '排行数值',
              `ratingNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '投票人数',
              `maxRate` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
              `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示',
              `buyable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放购买',
              `conversationId` varchar(255) NOT NULL DEFAULT '0',
              `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
              `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',

              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `classroom_courses` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `classroomId` int(10) unsigned NOT NULL COMMENT '班级ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `parentCourseId` INT(10) UNSIGNED NOT NULL COMMENT '父课程Id',
              `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
              `seq` INT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班级课程顺序',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `classroom_member` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
              `levelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
              `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记数',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题数',
              `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '学员是否被锁定',
              `remark` text COMMENT '备注',
              `role` VARCHAR(255) NOT NULL DEFAULT 'auditor' COMMENT '角色',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `classroom_review` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
              `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
              `content` text NOT NULL COMMENT '内容',
              `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分0-5',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `article_like` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
             `articleId` int(10) unsigned NOT NULL COMMENT '资讯id',
             `userId` int(10) unsigned NOT NULL COMMENT '用户id',
             `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞时间',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='资讯点赞表';

            CREATE TABLE IF NOT EXISTS `ip_blacklist` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `ip` varchar(32) NOT NULL,
            `type` ENUM('failed','banned') NOT NULL COMMENT '禁用类型',
            `counter` int(10) unsigned NOT NULL DEFAULT '0',
            `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            CREATE TABLE IF NOT EXISTS `blacklist` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
            `userId` int(10) unsigned NOT NULL COMMENT '名单拥有者id',
            `blackId` int(10) unsigned NOT NULL COMMENT '黑名单用户id',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入黑名单时间',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';

            CREATE TABLE IF NOT EXISTS `task` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) DEFAULT NULL COMMENT '任务标题',
            `description` text COMMENT '任务描述',
            `meta` text COMMENT '任务元信息',
            `userId` int(10) NOT NULL DEFAULT '0',
            `taskType` varchar(100) NOT NULL COMMENT '任务类型',
            `batchId` int(10) NOT NULL DEFAULT '0' COMMENT '批次Id',
            `targetId` int(10) NOT NULL DEFAULT '0' COMMENT '类型id,可以是课时id,作业id等',
            `targetType` varchar(100) DEFAULT NULL COMMENT '类型,可以是课时,作业等',
            `taskStartTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务开始时间',
            `taskEndTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务结束时间',
            `intervalDate` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '历时天数',
            `status` enum('active','completed') NOT NULL DEFAULT 'active',
            `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为必做任务,0否,1是',
            `completedTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务完成时间',
            `createdTime` int(10) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `batch_notification` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '群发通知id',
              `type` enum('text', 'image', 'video', 'audio')  NOT NULL DEFAULT 'text' COMMENT '通知类型' ,
              `title` text NOT NULL COMMENT '通知标题',
              `fromId` int(10) unsigned NOT NULL COMMENT '发送人id',
              `content` text NOT NULL COMMENT '通知内容',
              `targetType` text NOT NULL COMMENT '通知发送对象group,global,course,classroom等',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知发送对象ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送通知时间',
              `published` int(10) NOT NULL DEFAULT '0' COMMENT '是否已经发送',
              `sendedTime` int(10) NOT NULL DEFAULT '0' COMMENT '群发通知的发送时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='群发通知表';

            CREATE TABLE IF NOT EXISTS `card` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `cardId` varchar(255)  NOT NULL DEFAULT '' COMMENT '卡的ID',
              `cardType` varchar(255) NOT NULL DEFAULT '' COMMENT '卡的类型',
              `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间',
              `useTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
              `status` ENUM('used','receive','invalid','deleted') NOT NULL DEFAULT 'receive' COMMENT '使用状态',
              `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
              `createdTime` int(10) unsigned NOT NULL COMMENT '领取时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

            CREATE TABLE IF NOT EXISTS `coupon` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `code` varchar(255) NOT NULL COMMENT '优惠码',
              `type` enum('minus','discount') NOT NULL COMMENT '优惠方式',
              `status` enum('used','unused','receive') NOT NULL COMMENT '使用状态',
              `rate` float(10,2) unsigned NOT NULL COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
              `batchId` int(10) unsigned  NULL DEFAULT NULL COMMENT '批次号',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
              `deadline` int(10) unsigned NOT NULL COMMENT '失效时间',
              `targetType` varchar(64) NUll DEFAULT NULL COMMENT '使用对象类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用对象',
              `orderId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
              `orderTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
              `createdTime` int(10) unsigned NOT NULL,
              `receiveTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='优惠码表';

            CREATE TABLE IF NOT EXISTS `invite_record` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `inviteUserId` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请者',
              `invitedUserId` int(11) unsigned NULL DEFAULT NULL COMMENT '被邀请者',
              `inviteTime` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请时间',
              `inviteUserCardId` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请者获得奖励的卡的ID',
              `invitedUserCardId` int(11) unsigned NULL DEFAULT NULL COMMENT '被邀请者获得奖励的卡的ID',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='邀请记录表';

            CREATE TABLE IF NOT EXISTS `cash_change` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL,
              `amount` double(10,2) NOT NULL DEFAULT '0.00',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

            CREATE TABLE IF NOT EXISTS `recent_post_num` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
             `ip` varchar(20) NOT NULL COMMENT 'IP',
             `type` varchar(255) NOT NULL COMMENT '类型',
             `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'post次数',
             `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
             `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';

            CREATE TABLE IF NOT EXISTS  `user_pay_agreement` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(11) NOT NULL COMMENT '用户Id',
              `type` int(8) NOT NULL DEFAULT '0' COMMENT '0:储蓄卡1:信用卡',
              `bankName` varchar(255) NOT NULL COMMENT '银行名称',
              `bankNumber` int(8) NOT NULL COMMENT '银行卡号',
              `userAuth` varchar(225) DEFAULT NULL COMMENT '用户授权',
              `bankAuth` varchar(225) NOT NULL COMMENT '银行授权码',
              `bankId` int(8) NOT NULL COMMENT '对应的银行Id',
              `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
               PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户授权银行';

            CREATE TABLE IF NOT EXISTS  `marker` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `second` int(10) unsigned NOT NULL COMMENT '驻点时间',
              `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='驻点';

            CREATE TABLE IF NOT EXISTS  `question_marker` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
                `questionId` int(10) unsigned NOT NULL COMMENT '问题Id',
                `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
                `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
                `stem` text COMMENT '题干',
                `answer` text COMMENT '参考答案',
                `analysis` text COMMENT '解析',
                `metas` text COMMENT '题目元信息',
                `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='弹题';

            CREATE TABLE IF NOT EXISTS  `question_marker_result` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
                `questionMarkerId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '弹题ID',
                `lessonId` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做题人ID',
                `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none' COMMENT '结果状态',
                `answer` text  DEFAULT NULL ,
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS  `keyword` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(64) CHARACTER SET utf8 NOT NULL,
              `state` ENUM('replaced','banned') NOT NULL DEFAULT 'replaced',
              `bannedNum` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS  `keyword_banlog` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `keywordId` int(10) unsigned NOT NULL,
              `keywordName` varchar(64) NOT NULL DEFAULT '',
              `state` ENUM('replaced','banned') NOT NULL DEFAULT 'replaced',
              `text` text NOT NULL,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `ip` varchar(64) NOT NULL DEFAULT '',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `keywordId` (`keywordId`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `discovery_column` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL,
              `type` varchar(32) NOT NULL COMMENT '栏目类型',
              `categoryId` int(10) NOT NULL DEFAULT '0' COMMENT '分类',
              `orderType` varchar(32) NOT NULL COMMENT '排序字段',
              `showCount` int(10) NOT NULL COMMENT '展示数量',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
              `createdTime` int(10) unsigned NOT NULL,
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '发现页栏目';

            CREATE TABLE IF NOT EXISTS `upload_files_collection` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `fileId` int(10) unsigned NOT NULL COMMENT '文件Id',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏者',
              `createdTime` int(10) unsigned NOT NULL,
              `updatedTime` INT(10) unsigned NULL DEFAULT '0'  COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文件收藏表';

            CREATE TABLE IF NOT EXISTS `upload_files_tag` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统ID',
              `fileId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件ID',
              `tagId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件与标签的关联表';

            CREATE TABLE IF NOT EXISTS `upload_files_share_history` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统ID',
             `sourceUserId` int(10) NOT NULL COMMENT '分享用户的ID',
             `targetUserId` int(10) NOT NULL COMMENT '被分享的用户的ID',
             `isActive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '',
             `createdTime` int(10) DEFAULT '0' COMMENT '创建时间',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `cloud_data` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `body` text NOT NULL,
              `timestamp` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              `updatedTime` int(10) unsigned NOT NULL,
              `createdUserId` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `dictionary_item` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
             `type` varchar(255) NOT NULL COMMENT '字典类型',
             `code` varchar(64) DEFAULT NULL COMMENT '编码',
             `name` varchar(255) NOT NULL COMMENT '字典内容名称',
             `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
             `createdTime` int(10) unsigned NOT NULL,
             `updateTime` int(10) unsigned DEFAULT '0',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `dictionary` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
             `name` varchar(255) NOT NULL COMMENT '字典名称',
             `type` varchar(255) NOT NULL COMMENT '字典类型',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


            CREATE TABLE IF NOT EXISTS   `org` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组织机构ID',
              `name` varchar(255) NOT NULL COMMENT '名称',
              `parentId` int(11) NOT NULL DEFAULT '0' COMMENT '组织机构父ID',
              `childrenNum` tinyint(3) unsigned NOT NULL  DEFAULT  '0' COMMENT '辖下组织机构数量',
              `depth` int(11) NOT NULL   DEFAULT  '1' COMMENT '当前组织机构层级',
              `seq` int(11) NOT NULL  DEFAULT '0' COMMENT '索引',
              `description` text COMMENT '备注',
              `code` varchar(255) NOT NULL DEFAULT '' COMMENT '机构编码',
              `orgCode` varchar(255) NOT NULL DEFAULT '0' COMMENT '内部编码',
              `createdUserId` int(11) NOT NULL COMMENT '创建用户ID',
              `createdTime` int(11) unsigned NOT NULL  COMMENT '创建时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '最后更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `orgCode` (`orgCode`),
              UNIQUE KEY(`code`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='组织机构';

            CREATE TABLE IF NOT EXISTS `im_conversation` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `no` varchar(64) NOT NULL COMMENT 'IM云端返回的会话id',
                `memberIds` text NOT NULL COMMENT '会话中用户列表(用户id按照小到大排序，竖线隔开)',
                `memberHash` varchar(32) NOT NULL DEFAULT '' COMMENT 'memberIds字段的hash值，用于优化查询',
                `createdTime` int(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=`InnoDB` DEFAULT CHARACTER SET utf8 COMMENT='IM云端会话记录表';

            CREATE TABLE IF NOT EXISTS `im_my_conversation` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `no` varchar(64) NOT NULL,
                `userId` int(10) UNSIGNED NOT NULL,
                `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='用户个人的会话列表';

            CREATE TABLE IF NOT EXISTS `open_course_recommend` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `openCourseId` int(10) NOT NULL COMMENT '公开课id',
              `recommendCourseId` int(10) NOT NULL DEFAULT '0' COMMENT '推荐课程id',
              `seq` int(10) NOT NULL DEFAULT '0' COMMENT '序列',
              `type` varchar(255) NOT NULL COMMENT '类型',
              `createdTime` int(10) NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `open_course_recommend_openCourseId_index` (`openCourseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公开课推荐课程表';

            CREATE TABLE IF NOT EXISTS `open_course` (
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
              PRIMARY KEY (`id`),
              KEY `updatedTime` (`updatedTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

            CREATE TABLE IF NOT EXISTS `open_course_lesson` (
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
              `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated',
              `maxOnlineNum` INT NULL DEFAULT '0' COMMENT '直播在线人数峰值',
              `liveProvider` int(10) unsigned NOT NULL DEFAULT '0',
              `userId` int(10) unsigned NOT NULL COMMENT '发布人ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id',
              `suggestHours` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '建议学习时长',
              `testMode` ENUM('normal', 'realTime') NULL DEFAULT 'normal' COMMENT '考试模式',
              `testStartTime` INT(10) NULL DEFAULT '0' COMMENT '实时考试开始时间',
              PRIMARY KEY (`id`),
              KEY `updatedTime` (`updatedTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `open_course_member` (
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
              PRIMARY KEY (`id`),
              KEY `open_course_member_ip_courseId_index` (`ip`,`courseId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `referer_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `targetId` VARCHAR(64)  DEFAULT NUll COMMENT '模块ID',
              `targetType` varchar(64) NOT NULL COMMENT '模块类型',
              `targetInnerType` VARCHAR(64) NULL COMMENT '模块自身的类型',
              `refererUrl` VARCHAR(1024) DEFAULT '' COMMENT '访问来源Url',
              `refererHost` VARCHAR(1024) DEFAULT '' COMMENT '访问来源Url',
              `refererName` varchar(64)  DEFAULT '' COMMENT '访问来源站点名称',
              `orderCount` int(10) unsigned  DEFAULT '0'  COMMENT '促成订单数',
              `ip` VARCHAR(64) DEFAULT NULL  COMMENT '访问者IP',
              `userAgent` text COMMENT '浏览器的标识',
              `uri` VARCHAR(1024) DEFAULT '' COMMENT '访问Url',
              `createdUserId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问者',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问时间',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='模块(课程|班级|公开课|...)的访问来源日志';

            CREATE TABLE IF NOT EXISTS `order_referer_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `refererLogId` int(11) NOT NULL COMMENT '促成订单的访问日志ID',
              `orderId` int(10) unsigned  DEFAULT '0'  COMMENT '订单ID',
              `sourceTargetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
              `sourceTargetType` varchar(64) NOT NULL DEFAULT '' COMMENT '来源类型',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '订单的对象类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的对象ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '订单支付时间',
              `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付者',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单促成日志';

            CREATE TABLE IF NOT EXISTS `upgrade_notice` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(11) NOT NULL,
              `code` varchar(100) NOT NULL COMMENT '编码',
              `version` varchar(100) NOT NULL COMMENT '版本号',
              `createdTime` int(11) NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户升级提示查看';

            CREATE TABLE IF NOT EXISTS `order_referer` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `uv` VARCHAR(64) NOT NULL ,
              `data` text NOT NULL ,
              `orderIds` text,
              `expiredTime`  int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '过期时间',
              PRIMARY KEY (`id`),
              KEY `order_referer_uv_expiredTime_index` (`uv`,`expiredTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户访问日志Token';

            CREATE TABLE IF NOT EXISTS `file_used` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(32) NOT NULL,
                `fileId` int(11) NOT NULL COMMENT 'upload_files id',
                `targetType` varchar(32) NOT NULL,
                `targetId` int(11) NOT NULL,
                `createdTime` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `file_used_type_targetType_targetId_index` (`type`,`targetType`,`targetId`),
                KEY `file_used_type_targetType_targetId_fileId_index` (`type`,`targetType`,`targetId`,`fileId`),
                KEY `file_used_fileId_index` (`fileId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `course_lesson_extend` (
              `id` int(10) NOT NULL COMMENT '课时ID',
              `courseId` int(10) NOT NULL DEFAULT '0' COMMENT '课程ID',
              `doTimes` int(10) NOT NULL DEFAULT '0' COMMENT '可考试次数',
              `redoInterval` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '重做时间间隔(小时)'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课时扩展表';

            CREATE TABLE IF NOT EXISTS  `role` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(32)  NOT NULL COMMENT '权限名称',
              `code` varchar(32)  NOT NULL COMMENT '权限代码',
              `data` text COMMENT '权限配置',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `createdUserId` int(10) unsigned NOT NULL COMMENT '创建用户ID',
              `updatedTime`int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ";

        $container = $this->getContainer();
        $container['db']->exec($sql);
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
