<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->batchUpdate($index);
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

    protected function batchUpdate($index)
    {
        $this->c2courseSetMigrate();
        $this->c2courseMigrate();

        $this->c2CourseLessonMigrate();

    }

    protected function c2courseSetMigrate()
    {
        if (!$this->isTableExist('c2_course_set')) {
            $sql
                = "CREATE TABLE `c2_course_set` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `type` varchar(32) NOT NULL DEFAULT '',
                `title` varchar(1024) DEFAULT '',
                `subtitle` varchar(1024) DEFAULT '',
                `tags` text,
                `categoryId` int(10) NOT NULL DEFAULT '0',
                `summary` TEXT,
                `goals` TEXT,
                `audiences` TEXT,
                `cover` VARCHAR(1024),
                `status` varchar(32) DEFAULT '0' COMMENT 'draft, published, closed',
                `creator` int(11) DEFAULT '0',
                `createdTime` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
                `updatedTime` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
                `serializeMode` varchar(32) NOT NULL DEFAULT 'none' COMMENT 'none, serilized, finished',
                `ratingNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程评论数',
                `rating` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程评分',
                `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程笔记数',
                `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程学员数',
                `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
                `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
                `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
                `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
                `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
                `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT '折扣活动ID',
                `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣',
                `hitNum` int(10) unsigned NOT NULL DEFAULT  '0' COMMENT '课程点击数',
                `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
                `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
                `parentId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '是否班级课程',
                `locked` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否锁住',
                `minCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最低价格',
                `maxCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最高价格',
                `oldCourseId` int(11) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

            $result = $this->getConnection()->exec($sql);
        }

        $sql
                = "INSERT INTO `c2_course_set` (
            `oldCourseId`
            ,`title`
            ,`subtitle`
            ,`status`
            ,`type`
            ,`serializeMode`
            ,`rating`
            ,`ratingNum`
            ,`categoryId`
            ,`tags`
            ,`goals`
            ,`audiences`
            ,`recommended`
            ,`recommendedSeq`
            ,`recommendedTime`
            ,`studentNum`
            ,`hitNum`
            ,`discountId`
            ,`discount`
            ,`createdTime`
            ,`updatedTime`
            ,`parentId`
            ,`noteNum`
            ,`locked`
            ,`maxRate`
            ,`orgId`
            ,`orgCode`
            ,`cover`
            ,`creator`
            ,`summary`
        ) SELECT 
            `id`,
            ,`title`
            ,`subtitle`
            ,`status`
            ,`type`
            ,`serializeMode`
            ,`rating`
            ,`ratingNum`
            ,`categoryId`
            ,`tags`
            ,`goals`
            ,`audiences`
            ,`recommended`
            ,`recommendedSeq`
            ,`recommendedTime`
            ,`studentNum`
            ,`hitNum`
            ,`discountId`
            ,`discount`
            ,`createdTime`
            ,`updatedTime`
            ,`parentId`
            ,`noteNum`
            ,`locked`
            ,`maxRate`
            ,`orgId`
            ,`orgCode`
            ,concat('{\"large\":\"',largePicture,'\",\"middle\":\"',middlePicture,'\",\"small\":\"',smallPicture,'\"}') as cover
            ,`userId`
            ,`about`
        FROM `course` where `id` not in (select `oldCourseId` from `c2_course_set`);";
        $result = $this->getConnection()->exec($sql);

        $sql    = "UPDATE `c2_course_set` AS `c` SET `c`.`parentId` =  (select `id` from `c2_course_set` where `oldCourseId` = `c`.`oldCourseId`)";
        $result = $this->getConnection()->exec($sql);

        $sql    = "UPDATE `c2_course_set` ce, (SELECT count(id) AS num , courseSetId FROM `course_material` GROUP BY courseSetId) cm  SET ce.`materialNum` = cm.num  WHERE ce.id = cm.`courseSetId`;";
        $result = $this->getConnection()->exec($sql);
    }

    protected function c2courseMigrate()
    {
        if (!$this->isTableExist('c2_course')) {
            $sql
                = "CREATE TABLE `c2_course` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `courseSetId` int(11) NOT NULL,
                  `title` varchar(1024) DEFAULT NULL,
                  `learnMode` varchar(32) DEFAULT NULL COMMENT 'lockMode, freeMode',
                  `expiryMode` varchar(32) DEFAULT NULL COMMENT 'days, date',
                  `expiryDays` int(11) DEFAULT NULL,
                  `expiryStartDate` int(11) DEFAULT NULL,
                  `expiryEndDate` int(11) DEFAULT NULL,
                  `summary` text,
                  `goals` text,
                  `audiences` text,
                  `isDefault` tinyint(1) DEFAULT '0',
                  `maxStudentNum` int(11) DEFAULT '0',
                  `status` varchar(32) DEFAULT NULL COMMENT 'draft, published, closed',
                  `creator` int(11) DEFAULT NULL,
                  `isFree` tinyint(1) DEFAULT 0,
                  `price` float(10,2) NULL DEFAULT '0',
                  `vipLevelId` int(11) DEFAULT 0,
                  `buyable` tinyint(1) DEFAULT 1,
                  `tryLookable` tinyint(1) DEFAULT 0,
                  `tryLookLength` int(11) DEFAULT 0,
                  `watchLimit` int(11) DEFAULT 0,
                  `services` text,
                  `taskNum` int(10) DEFAULT 0 COMMENT '任务数',
                  `studentNum` int(10) DEFAULT 0 COMMENT '学员数',
                  `teacherIds` VARCHAR(1024) DEFAULT 0 COMMENT '可见教师ID列表',
                  `parentId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程的父Id',
                  `createdTime` INT(10) UNSIGNED NOT NULL COMMENT '课程创建时间',
                  `updatedTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `ratingNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程计划评论数',
                  `rating` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程计划评分',
                  `noteNum` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                  `buyExpiryTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买开放有效期',
                  `threadNum` int(10) DEFAULt 0 COMMENT '话题数',
                  `type` varchar(32) NOT NULL DEFAULT 'normal' COMMENT '教学计划类型',
                  `approval` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否需要实名才能购买',
                  `income` float(10,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总收入',
                  `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程人民币原价',
                  `coinPrice` float(10,2) NOT NULL DEFAULT '0.00',
                  `originCoinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程虚拟币原价',
                  `showStudentNumType` enum('opened','closed') NOT NULL DEFAULT 'opened' COMMENT '学员数显示模式',
                  `serializeMode` VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT 'none, serilized, finished',
                  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课程所有课时，可获得的总学分',
                  `about` text COMMENT '简介',
                  `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID',
                  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '上课地区地址',
                  `deadlineNotify` enum('active','none') NOT NULL DEFAULT 'none' COMMENT '开启有效期通知',
                  `daysOfNotifyBeforeDeadline` int(10) NOT NULL DEFAULT '0',
                  `useInClassroom` enum('single','more') NOT NULL DEFAULT 'single' COMMENT '课程能否用于多个班级',
                  `singleBuy` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '加入班级后课程能否单独购买',
                  `freeStartTime` int(10) NOT NULL DEFAULT '0',
                  `freeEndTime` int(10) NOT NULL DEFAULT '0',
                  `locked` int(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁',
                  `cover` VARCHAR(1024),
                  `enableFinish` INT(1) NOT NULL DEFAULT '1' COMMENT '是否允许学院强制完成任务',
                  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
                  `maxRate` tinyint(3) DEFAULT 0 COMMENT '最大抵扣百分比',
                  `publishedTaskNum` INT(10) DEFAULT '0' COMMENT '已发布的任务数',
                  `oldCourseId` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

            $result = $this->getConnection()->exec($sql);
        }

        $sql
                = "INSERT INTO `c2_course` (
            `oldCourseId`
            ,`title`
            ,`status`
            ,`type`
            ,`maxStudentNum`
            ,`price`
            ,`originCoinPrice`
            ,`coinPrice`
            ,`originPrice`
            ,`expiryMode`
            ,`showStudentNumType`
            ,`serializeMode`
            ,`income`
            ,`giveCredit`
            ,`rating`
            ,`ratingNum`
            ,`about`
            ,`teacherIds`
            ,`goals`
            ,`audiences`
            ,`locationId`
            ,`address`
            ,`studentNum`
            ,`deadlineNotify`
            ,`daysOfNotifyBeforeDeadline`
            ,`useInClassroom`
            ,`watchLimit`
            ,`singleBuy`
            ,`createdTime`
            ,`updatedTime`
            ,`freeStartTime`
            ,`freeEndTime`
            ,`approval`
            ,`parentId`
            ,`noteNum`
            ,`locked`
            ,`buyable`
            ,`buyExpiryTime`
            ,`tryLookable`
            ,`summary`
            ,`cloneId`
            ,`cover`
            ,`creator`
            ,`vipLevelId`
            ,`tryLookLength`
            ,`taskNum`
            ,`copyCourseId`
            ,`isDefault`
            ,`isFree`
            ,`threadNum`
            ,`enableFinish`
            ,`learnMode`
        ) SELECT 
            `id`
            ,`title`
            ,`status`
            ,`type`
            ,`maxStudentNum`
            ,`price`
            ,`originCoinPrice`
            ,`coinPrice`
            ,`originPrice`
            ,`expiryMode`
            ,`showStudentNumType`
            ,`serializeMode`
            ,`income`
            ,`giveCredit`
            ,`rating`
            ,`ratingNum`
            ,`about`
            ,`teacherIds`
            ,`goals`
            ,`audiences`
            ,`locationId`
            ,`address`
            ,`studentNum`
            ,`deadlineNotify`
            ,`daysOfNotifyBeforeDeadline`
            ,`useInClassroom`
            ,`watchLimit`
            ,`singleBuy`
            ,`createdTime`
            ,`updatedTime`
            ,`freeStartTime`
            ,`freeEndTime`
            ,`approval`
            ,`parentId`
            ,`noteNum`
            ,`locked`
            ,`buyable`
            ,`buyExpiryTime`
            ,`tryLookable`
            ,`about`
            ,`parentId` as `cloneId`
            ,concat('{\"large\":\"',largePicture,'\",\"middle\":\"',middlePicture,'\",\"small\":\"',smallPicture,'\"}') as cover
            ,`userId` as `creator`
            ,`vipLevelId`
            ,`tryLookTime`
            ,`lessonNum` as `taskNum`
            ,`parentId` as `copyCourseId`
            ,1
            ,case when `originPrice` = 0 then 1 else 0 end
            ,0
            ,1
            ,'freeMode'
        FROM `course` where `id` not in (select `oldCourseId` from `c2_course`);";
        $result = $this->getConnection()->exec($sql);

        $sql    = "UPDATE `c2_course` AS `c` SET `c`.`courseSetId` =  (select `id` from `c2_course_set` where `oldCourseId` = `c`.`oldCourseId`)";
        $result = $this->getConnection()->exec($sql);

        $sql    = "UPDATE `c2_course` AS `c` SET `c`.`parentId` =  (select `id` from `c2_course` where `oldCourseId` = `c`.`oldCourseId`)";
        $result = $this->getConnection()->exec($sql);

        $sql    = "UPDATE `c2_course` AS `c` SET `c`.`copyCourseId` = `c`.`parentId`";
        $result = $this->getConnection()->exec($sql);

        $sql    = "UPDATE `c2_course` AS `c` SET `c`.`cloneId` = `c`.`parentId`";
        $result = $this->getConnection()->exec($sql);

        $sql    = "UPDATE `c2_course` ce, (SELECT count(id) AS num , courseId FROM `course_material` GROUP BY courseId) cm  SET ce.`materialNum` = cm.num  WHERE ce.id = cm.courseId;";
        $result = $this->getConnection()->exec($sql);

        $sql
                = "UPDATE `c2_course_set` cs, `c2_course` c, 
        SET cs.minCoursePrice = c.price, cs.maxCoursePrice = c.price where c.courseSetId = cs.id";
        $result = $this->getConnection()->exec($sql);
    }


    /**
     * 课时数据升级 包含了 task, activity, activityExt
     *
     * 为了保证数据准确性，扩展表中添加了lessonId
     */
    protected function c2CourseLessonMigrate()
    {
        $this->c2CourseTaskMigrate();
        $this->c2Activity();

        $this->c2VideoActivity();
        $this->c2TextActivity();
        $this->c2AudioActivity();
        $this->c2FlashActivity();
        $this->c2PPtActivity();
        $this->c2DocActivity();
        $this->c2TestPaperActivity();

        $this->c2CourseTaskView();

        $this->c2CourseTaskResult();
    }

    /**
     * taskId 与lessonId一直
     */
    protected function c2CourseTaskMigrate()
    {

        if (!$this->isTableExist('course_task')) {
            $this->getConnection()->exec("
                  CREATE TABLE `course_task` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
                  `fromCourseSetId` int(10) unsigned NOT NULL DEFAULT '0',
                  `seq` int(10) unsigned NOT NULL,
                  `categoryId` int(10) DEFAULT NULL,
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '引用的教学活动',
                  `title` varchar(255) NOT NULL COMMENT '标题',
                  `isFree` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否免费',
                  `isOptional` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否必修',
                  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
                  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
                  `status` varchar(255) NOT NULL DEFAULT 'create' COMMENT '发布状态 create|publish|unpublish',
                  `createdUserId` int(10) unsigned NOT NULL COMMENT '创建者',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `mode` varchar(60) DEFAULT NULL COMMENT '任务模式',
                  `number` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务编号',
                  `type` varchar(50) NOT NULL COMMENT '任务类型',
                  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
                  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义',
                  `maxOnlineNum` int(11) unsigned DEFAULT '0' COMMENT '任务最大可同时进行的人数，0为不限制',
                  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源task的id',
                  PRIMARY KEY (`id`),
                  KEY `seq` (`seq`)
                ) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8;
            ");
        }

        $this->getConnection()->exec("
            insert into course_task(
                 `id`,
                 `courseId`,
                 `seq`,
                 `categoryId`,
                 `title`,
                 `isFree`,
                 `startTime`,
                 `endTime`,
                 `status`,
                 `createdUserId`,
                 `createdTime`,
                 `updatedTime`,
                 `mode` ,
                 `number`,
                 `type`,
                 `mediaSource` ,
                 `length` ,
                 `maxOnlineNum`,
                 `copyId`
            ) select 
                `id`,
                `courseId`,
                `seq`,
                `chapterId`,
                `title`, 
                `free`,
                `startTime`,
                `endTime`,
                `status`,
                `userId`,
                `createdTime`,
                `updatedTime`,
                'lesson',
                `number`, 
                `type`,
                `mediaSource`,
                `length`,
                `maxOnlineNum`,
                `copyId` 
            from `course_lesson` WHERE `id` NOT IN (SELECT id FROM `course_task`)
        ");

        $this->getConnection()->exec("update `course_task` AS  `ct` set `ct`.fromCourseSetId   = (select `courseSetId` from `c2_course` AS `ce` where `ct`.courseId = `ce`.id)");
    }

    protected function c2Activity()
    {

        if (!$this->isTableExist('activity')) {
            $this->getConnection()->exec("
             CREATE TABLE `activity` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `title` varchar(255) NOT NULL COMMENT '标题',
                  `remark` text,
                  `mediaId` int(10) unsigned DEFAULT '0' COMMENT '教学活动详细信息Id，如：视频id, 教室id',
                  `mediaType` varchar(50) NOT NULL COMMENT '活动类型',
                  `content` text COMMENT '活动描述',
                  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义',
                  `fromCourseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属教学计划',
                  `fromCourseSetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属的课程',
                  `fromUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者的ID',
                  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
                  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源activity的id',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8;
            ");

            $this->getConnection()->exec("
            insert into `activity`(
                `id`,
                `title` ,
                `remark` ,
                `mediaId` ,
                `mediaType`,
                `content`,
                `length`,
                `fromCourseId`,
                `fromUserId`,
                `startTime`,
                `endTime`, 
                `createdTime`,
                `updatedTime`,
                `copyId`
            )select 
                `id`,
                `title`,
                `summary`,
                `mediaId`,
                CASE WHEN `type` = 'document' THEN 'doc'  ELSE TYPE END AS 'type',
                `content`,
                `length`,
                `courseId`,
                `userId`,
                `startTime`,
                `endTime`,
                `createdTime`,
                `updatedTime`,
                `copyId`
            from `course_lesson` where `id` not in (select id from `activity`);

        ");

            //update activityId in table course_task
            $this->getConnection()->exec("update `course_task`  ck set  `activityId` = (select `id` from  `activity` ay  where  ck.id = ay.id)
            ");
            //courseSetId
            $this->getConnection()->exec("update `activity` AS  `ct` set `ct`.fromCourseSetId   = (select `courseSetId` from `c2_course` AS `ce` where `ct`.courseId = `ce`.id)");
        }
    }

    protected function c2VideoActivity()
    {
        if (!$this->isTableExist('video_activity')) {
            $this->getConnection()->exec("
                CREATE TABLE `video_activity` (
                  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
                  `mediaId` int(10) NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
                  `mediaUri` text COMMENT '媒体文件资UR',
                  `finishType` varchar(60) DEFAULT NULL COMMENT '完成类型',
                  `finishDetail` text COMMENT '完成条件',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';
            ");
        }
        if (!$this->isFieldExist('video_activity', 'lessonId')) {
            $this->exec("alter table `video_activity` add `lessonId` int(10) ;");
        }
        $this->getConnection()->exec("
            insert into `video_activity` (
                `mediaSource`, 
                `mediaId`,
                `mediaUri`,
                `finishType`,
                `finishDetail`,
                `lessonId` 
            )
            select 
                `mediaSource`,
                `mediaId`,
                `mediaUri`,
                'end',
                '1',
                `id` 
            from `course_lesson` where  type ='video' and   `id` not in (select `lessonId` from `video_activity`);
        ");

        $this->getConnection()->exec("
            UPDATE  `activity` AS ay ,`video_activity` AS vy   SET ay.`mediaId`  =  vy.id   
            WHERE ay.id  = vy.lessonId   AND ay.`mediaType` = 'video';
        ");

    }

    protected function c2TextActivity()
    {
        if (!$this->isTableExist("text_activity")) {
            $this->getConnection()->exec("
            CREATE TABLE `text_activity` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, time',
              `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
              `createdTime` int(10) NOT NULL,
              `createdUserId` int(11) NOT NULL,
              `updatedTime` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('text_activity', 'lessonId')) {
            $this->exec("alter table `text_activity` add `lessonId` int(10) ;");
        }

        $this->getConnection()->exec("
            INSERT INTO `text_activity` (
                `finishType`,
                `finishDetail`,
                `createdTime`,
                `createdUserId`,
                `updatedTime`,
                `lessonId` 
            )
            SELECT 
                'time',
                '1',
                `createdTime`,
                `userId`,
                `updatedTime`,
                `id` 
            FROM `course_lesson` WHERE  `type`='text' AND  `id` NOT IN (SELECT `lessonId` FROM `text_activity`);
        ");

        $this->getConnection()->exec("
             UPDATE  `activity` AS ay ,`text_activity` AS ty   SET ay.`mediaId`  =  ty.id   
             WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'text';
        ");
    }

    protected function c2AudioActivity()
    {
        if (!$this->isTableExist('audio_activity')) {
            $this->exec("
                CREATE TABLE `audio_activity` (
                  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `mediaId` int(10) DEFAULT NULL COMMENT '媒体文件ID',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='音频活动扩展表';
            ");
        }

        if (!$this->isFieldExist('audio_activity', 'lessonId')) {
            $this->exec("alter table `audio_activity` add `lessonId` int(10) ;");
        }

        $this->exec("
            insert into `audio_activity` 
            (
                `mediaId`,
                `lessonId`
            )
            select 
              `mediaId`,
              `id`
            from `course_lesson` where  type ='audio' and   `id` not in (select `lessonId` from `audio_activity`);
        ");

        $this->exec("
          UPDATE  `activity` AS ay ,`audio_activity` AS ty   SET ay.`mediaId`  =  ty.id   
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'audio';
         ");

        // $this->getConnection()->exec("alter table `audio_activity` add `lessonId` int(10) ;");
    }

    protected function c2FlashActivity()
    {
        if (!$this->isTableExist("flash_activity")) {
            $this->exec("
                CREATE TABLE `flash_activity` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `mediaId` int(11) NOT NULL,
                  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, time',
                  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
                  `createdTime` int(10) NOT NULL,
                  `createdUserId` int(11) NOT NULL,
                  `updatedTime` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('flash_activity', 'lessonId')) {
            $this->exec("alter table `flash_activity` add `lessonId` int(10) ;");
        }

        $this->exec("
            INSERT INTO `flash_activity`
            (
            `mediaId`,
            `finishType`,
            `finishDetail`,
            `createdTime`,
            `createdUserId`,
            `updatedTime`,
            `lessonId`
            )
            SELECT 
                `mediaId`,
                'time',
                '1',
                `createdTime`,
                `userId` ,
                `updatedTime`,
                `id`
            FROM `course_lesson` WHERE TYPE ='flash' AND id NOT IN (SELECT `lessonId` FROM `flash_activity`)
        ");

        $this->exec("
          UPDATE  `activity` AS ay ,`flash_activity` AS ty   SET ay.`mediaId`  =  ty.id   
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'flash';
         ");

    }

    protected function c2PPtActivity()
    {

        if (!$this->isTableExist('ppt_activity')) {
            $this->exec("
                CREATE TABLE `ppt_activity` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `mediaId` int(11) NOT NULL,
                  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'end, time',
                  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
                  `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
                  `createdUserId` int(11) NOT NULL,
                  `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('ppt_activity', 'lessonId')) {
            $this->exec("alter table `ppt_activity` add `lessonId` int(10) ;");
        }

        $this->exec("
            insert into `ppt_activity`
            (
            `mediaId`,
            `finishType`,
            `finishDetail`,
            `createdTime`,
            `createdUserId`,
            `updatedTime`,
            `lessonId`
            )
            select 
                `mediaId`,
                'end',
                '1',
                `createdTime`,
                `userId` ,
                `updatedTime`,
                `id`
            from `course_lesson` where type ='ppt' and id not in (select `lessonId` from `ppt_activity`);
        ");

        $this->exec("
          UPDATE  `activity` AS ay ,`ppt_activity` AS ty   SET ay.`mediaId`  =  ty.id   
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'ppt';
         ");
    }

    protected function c2DocActivity()
    {
        if (!$this->isTableExist('doc_activity')) {
            $this->exec("
                CREATE TABLE `doc_activity` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `mediaId` int(11) NOT NULL,
                  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, detail',
                  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
                  `createdTime` int(10) NOT NULL,
                  `createdUserId` int(11) NOT NULL,
                  `updatedTime` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('doc_activity', 'lessonId')) {
            $this->exec("alter table `doc_activity` add `lessonId` int(10) ;");
        }

        $this->exec("
            INSERT INTO `doc_activity`
            (
            `mediaId`,
            `finishType`,
            `finishDetail`,
            `createdTime`,
            `createdUserId`,
            `updatedTime`,
            `lessonId`
            )
            SELECT 
                `mediaId`,
                'time',
                '1',
                `createdTime`,
                `userId` ,
                `updatedTime`,
                `id`
            FROM `course_lesson` WHERE TYPE ='document' AND id NOT IN (SELECT `lessonId` FROM `doc_activity`)
        ");


        $this->exec("
          UPDATE  `activity` AS ay ,`doc_activity` AS ty   SET ay.`mediaId`  =  ty.id   
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'doc';
         ");

    }

    protected function c2TestPaperActivity()
    {
        if (!$this->isTableExist("testpaper_activity")) {
            $this->exec(" 
                CREATE TABLE `testpaper_activity` (
                `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '关联activity表的ID',
                  `mediaId` int(10) NOT NULL DEFAULT '0' COMMENT '试卷ID',
                  `doTimes` smallint(6) NOT NULL DEFAULT '0' COMMENT '考试次数',
                  `redoInterval` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '重做时间间隔(小时)',
                  `limitedTime` int(10) NOT NULL DEFAULT '0' COMMENT '考试时间',
                  `checkType` text,
                  `finishCondition` text,
                  `requireCredit` int(10) NOT NULL DEFAULT '0' COMMENT '参加考试所需的学分',
                  `testMode` varchar(50) NOT NULL DEFAULT 'normal' COMMENT '考试模式',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('testpaper_activity', 'lessonId')) {
            $this->exec("alter table `testpaper_activity` add `lessonId` int(10) ;");
        }
        //王丹月做 testpaper_activity

        $this->exec("
          UPDATE  `activity` AS ay ,`testpaper_activity` AS ty   SET ay.`mediaId`  =  ty.id   
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'testpaper';
         ");

    }

    protected function c2CourseTaskView()
    {
        if (!$this->isTableExist('course_task_view')) {
            $this->exec("
                CREATE TABLE `course_task_view` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `courseSetId` int(10) NOT NULL,
                  `courseId` int(10) NOT NULL,
                  `taskId` int(10) NOT NULL,
                  `fileId` int(10) NOT NULL,
                  `userId` int(10) NOT NULL,
                  `fileType` varchar(80) NOT NULL,
                  `fileStorage` varchar(80) NOT NULL,
                  `fileSource` varchar(32) NOT NULL,
                  `createdTime` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        $this->exec("
            INSERT INTO `course_task_view`
            (
                `id`,
                `courseSetId`,
                `courseId`,
                `taskId`,
                `fileId`,
                `userId`,
                `fileType`,
                `fileStorage`,
                `fileSource`,
                `createdTime`
            )
            SELECT 
                `id`,
                0,
                `courseId`,
                `lessonId`,
                `fileId`,
                `userId`,
                `fileType`,
                `fileStorage`,
                `fileSource`,
                `createdTime`
            FROM `course_lesson_view` WHERE id NOT IN (SELECT id FROM `course_task_view`);
        ");

        $this->exec("UPDATE `course_task_view` AS cw , `c2_course` c SET cw.`courseSetId` = c.`courseSetId` WHERE cw.`courseId` = c.id ;");

    }

    protected function c2CourseTaskResult()
    {
        if (!$this->isTableExist('course_task_result')) {
            $this->exec("
                CREATE TABLE `course_task_result` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动的id',
                  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
                  `courseTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的任务id',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `status` varchar(255) NOT NULL DEFAULT 'start' COMMENT '任务状态，start，finish',
                  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务进行时长（分钟）',
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;
            ");
        }

        $this->exec("
            insert into `course_task_result`
            (
                `id`,
                `courseId`,
                `courseTaskId`,
                `userId`, 
                `status`,
                `finishedTime`,
                `createdTime`,
                `updatedTime`,
                `time`,
                `watchTime`
            )
            select 
                `id`,
                `courseId`,
                `lessonId`,
                `userId`,
                case when `status` = 'finished' then 'finish' else 'start' end AS 'status',
                `finishedTime`,
                `updateTime`,
                `updateTime`,
                `learnTime`,
                `watchTime`
            from `course_lesson_learn` where id not in (select id from `course_task_result`);
        ");


        $this->exec("
            UPDATE `course_task_result` cl,  `course_task` ck SET cl.`activityId`= ck.`activityId` WHERE cl.`courseTaskId` = ck.`id`;
        ");
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param string $statement
     *
     * @return integer The number of affected rows.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function exec($statement)
    {
        return $this->getConnection()->exec($statement);
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

    /**
     * @return \Topxia\Service\System\Impl\SettingServiceImpl
     */
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

    /**
     * @return \Topxia\Service\Common\Connection
     */
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
