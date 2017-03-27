<?php

class CourseMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('c2_course')) {
            $sql = "CREATE TABLE `c2_course` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `courseSetId` int(11) NOT NULL DEFAULT 0,
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
                  `ratingNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程计划评论数',
                  `rating` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程计划评分',
                  `noteNum` INT(10) UNSIGNED NOT NULL DEFAULT 0,
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
                  `buyExpiryTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买开放有效期',
                  `enableFinish` INT(1) NOT NULL DEFAULT '1' COMMENT '是否允许学院强制完成任务',
                  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
                  `maxRate` tinyint(3) DEFAULT 0 COMMENT '最大抵扣百分比',
                  `publishedTaskNum` INT(10) DEFAULT '0' COMMENT '已发布的任务数',
                  `createdTime` INT(10) UNSIGNED NOT NULL COMMENT '课程创建时间',
                  `updatedTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `creator` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $result = $this->getConnection()->exec($sql);
        }

        $sql = "INSERT INTO `c2_course` (
              `id`
              ,`courseSetId`
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
              ,`cover`
              ,`creator`
              ,`vipLevelId`
              ,`tryLookLength`
              ,`taskNum`
              ,`isDefault`
              ,`isFree`
              ,`threadNum`
              ,`enableFinish`
              ,`learnMode`
              ,`maxRate`
          ) SELECT
              `id`
              ,`id`
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
              ,concat('{\"large\":\"',largePicture,'\",\"middle\":\"',middlePicture,'\",\"small\":\"',smallPicture,'\"}') as cover
              ,`userId` as `creator`
              ,`vipLevelId`
              ,`tryLookTime`
              ,`lessonNum` as `taskNum`
              ,1
              ,case when `originPrice` = 0 then 1 else 0 end
              ,0
              ,1
              ,'freeMode'
              ,`maxRate`
          FROM `course` where `id` not in (select `id` from `c2_course`);";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course` ce, `c2_course_set` cs  SET ce.`materialNum` = cs.`materialNum`  WHERE ce.`id` = cs.`id`";
        $result = $this->getConnection()->exec($sql);

        // refactor: 这条语句不该在这个步骤中执行，应该在迁移完 course_task 后执行
        $sql = "UPDATE `c2_course` c set `publishedTaskNum` = (select count(*) from course_lesson where courseId=c.id and status = 'published')";
        $result = $this->getConnection()->exec($sql);
    }
}
