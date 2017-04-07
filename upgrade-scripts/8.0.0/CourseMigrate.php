<?php

class CourseMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('course_v8')) {
            $sql = "
              CREATE TABLE `course_v8` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `courseSetId` int(11) NOT NULL,
                `title` varchar(1024) DEFAULT NULL,
                `learnMode` varchar(32) DEFAULT NULL COMMENT 'lockMode, freeMode',
                `expiryMode` varchar(32) DEFAULT NULL COMMENT 'days, date',
                `expiryDays` int(11) DEFAULT NULL,
                `expiryStartDate` int(10) unsigned DEFAULT NULL,
                `expiryEndDate` int(10) unsigned DEFAULT NULL,
                `summary` text,
                `goals` text,
                `audiences` text,
                `isDefault` tinyint(1) DEFAULT '0',
                `maxStudentNum` int(11) DEFAULT '0',
                `status` varchar(32) DEFAULT NULL COMMENT 'draft, published, closed',
                `creator` int(11) DEFAULT NULL,
                `isFree` tinyint(1) DEFAULT '0',
                `price` float(10,2) DEFAULT '0.00',
                `vipLevelId` int(11) DEFAULT '0',
                `buyable` tinyint(1) DEFAULT '1',
                `tryLookable` tinyint(1) DEFAULT '0',
                `tryLookLength` int(11) DEFAULT '0',
                `watchLimit` int(11) DEFAULT '0',
                `services` text,
                `taskNum` int(10) DEFAULT '0' COMMENT '任务数',
                `publishedTaskNum` int(10) DEFAULT '0' COMMENT '已发布的任务数',
                `studentNum` int(10) DEFAULT '0' COMMENT '学员数',
                `teacherIds` varchar(1024) DEFAULT '0' COMMENT '可见教师ID列表',
                `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的父Id',
                `createdTime` int(10) unsigned NOT NULL COMMENT '课程创建时间',
                `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程计划评论数',
                `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '课程计划评分',
                `noteNum` int(10) unsigned NOT NULL DEFAULT '0',
                `buyExpiryTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买开放有效期',
                `threadNum` int(10) DEFAULT '0' COMMENT '话题数',
                `type` varchar(32) NOT NULL DEFAULT 'normal' COMMENT '教学计划类型',
                `approval` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要实名才能购买',
                `income` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总收入',
                `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程人民币原价',
                `coinPrice` float(10,2) NOT NULL DEFAULT '0.00',
                `originCoinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程虚拟币原价',
                `showStudentNumType` enum('opened','closed') NOT NULL DEFAULT 'opened' COMMENT '学员数显示模式',
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
                `cover` varchar(1024) DEFAULT NULL,
                `enableFinish` int(1) NOT NULL DEFAULT '1' COMMENT '是否允许学院强制完成任务',
                `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
                `maxRate` tinyint(3) DEFAULT '0' COMMENT '最大抵扣百分比',
                `serializeMode` varchar(32) NOT NULL DEFAULT 'none' COMMENT 'none, serilized, finished',
                `showServices` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否在营销页展示服务承诺',
                `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
                `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
                `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
                `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
                `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击量',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $result = $this->getConnection()->exec($sql);
        }

        if (!$this->isIndexExist('course_v8', 'courseSetId')) {
            $this->getConnection()->exec("
                ALTER TABLE course_v8 ADD INDEX courseSetId (`courseSetId`);
            ");
        }

        if (!$this->isIndexExist('course_v8', 'courseSetId_status')) {
            $this->getConnection()->exec("
                ALTER TABLE course_v8 ADD INDEX courseSetId_status (`courseSetId`,`status`);
            ");
        }

        $nextPage = $this->insertData($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateDate();
    }

    private function updateDate()
    {
        $sql = 'UPDATE `course_v8` ce, `course_set_v8` cs  SET ce.`materialNum` = cs.`materialNum`  WHERE ce.`id` = cs.`id`';
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `course_v8` c2, `course` c set
               c2.`status` = c.`status`
              ,c2.`type` = c.`type`
              ,c2.`maxStudentNum` = c.`maxStudentNum`
              ,c2.`price` = c.`price`
              ,c2.`originCoinPrice` = c.`originCoinPrice`
              ,c2.`coinPrice` = c.`coinPrice`
              ,c2.`originPrice` = c.`originPrice`
              ,c2.`expiryMode` = (case when c.`expiryMode` = 'none' then 'forever' else c.`expiryMode` end)
              ,c2.`expiryDays` = (case when c.`expiryMode` = 'days' then c.`expiryDay` end)
              ,c2.`expiryEndDate` = (case when c.`expiryMode` = 'date' then c.`expiryDay` end)
              ,c2.`showStudentNumType` = c.`showStudentNumType`
              ,c2.`serializeMode` = (case when c.`serializeMode` = 'serialize' then 'serialized' else c.`serializeMode` end)
              ,c2.`income` = c.`income`
              ,c2.`giveCredit` = c.`giveCredit`
              ,c2.`rating` = c.`rating`
              ,c2.`ratingNum` = c.`ratingNum`
              ,c2.`about` = c.`about`
              ,c2.`teacherIds` = c.`teacherIds`
              ,c2.`goals` = c.`goals`
              ,c2.`audiences` = c.`audiences`
              ,c2.`locationId` = c.`locationId`
              ,c2.`address` = c.`address`
              ,c2.`studentNum` = c.`studentNum`
              ,c2.`deadlineNotify` = c.`deadlineNotify`
              ,c2.`daysOfNotifyBeforeDeadline` = c.`daysOfNotifyBeforeDeadline`
              ,c2.`useInClassroom` = c.`useInClassroom`
              ,c2.`watchLimit` = c.`watchLimit`
              ,c2.`singleBuy` = c.`singleBuy`
              ,c2.`createdTime` = c.`createdTime`
              ,c2.`updatedTime` = c.`updatedTime`
              ,c2.`freeStartTime` = c.`freeStartTime`
              ,c2.`freeEndTime` = c.`freeEndTime`
              ,c2.`approval` = c.`approval`
              ,c2.`parentId` = c.`parentId`
              ,c2.`noteNum` = c.`noteNum`
              ,c2.`locked` = c.`locked`
              ,c2.`buyable` = c.`buyable`
              ,c2.`buyExpiryTime` = c.`buyExpiryTime`
              ,c2.`tryLookable` = c.`tryLookable`
              ,c2.`summary` = c.`about`
              ,c2.`cover` = concat('{\"large\":\"',c.largePicture,'\",\"middle\":\"',c.middlePicture,'\",\"small\":\"',c.smallPicture,'\"}')
              ,c2.`creator` = c.`userId`
              ,c2.`vipLevelId` = c.`vipLevelId`
              ,c2.`tryLookLength` = c.`tryLookTime`
              ,c2.`taskNum` = c.`lessonNum`
              ,c2.`isFree` = case when c.`originPrice` = 0 then 1 else 0 end
              ,c2.`maxRate` = c.`maxRate`
              ,c2.`recommended` = c.`recommended`
              ,c2.`recommendedSeq` = c.`recommendedSeq`
              ,c2.`recommendedTime` = c.`recommendedTime`
          where c2.`id` = c.`id` and c2.`updatedTime` < c.`updatedTime`;
        ";

        $result = $this->getConnection()->exec($sql);
    }

    private function insertData($page)
    {
        $countSql = 'SELECT count(*) FROM `course` where `id` not in (select `id` from `course_v8`)';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $sql = "INSERT INTO `course_v8` (
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
              ,`expiryDays`
              ,`expiryStartDate`
              ,`expiryEndDate`
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
              ,`hitNum`
              ,`recommended`
              ,`recommendedSeq`
              ,`recommendedTime`
          ) SELECT
              `id`
              ,`id`
              ,'默认教学计划'
              ,`status`
              ,`type`
              ,`maxStudentNum`
              ,`price`
              ,`originCoinPrice`
              ,`coinPrice`
              ,`originPrice`
              ,(case when `expiryMode` = 'none' then 'forever' else `expiryMode` end)
              ,(case when `expiryMode` = 'days' then `expiryDay` end)
              ,(case when `expiryMode` = 'date' then `createdTime` end)
              ,(case when `expiryMode` = 'date' then `expiryDay` end)
              ,`showStudentNumType`
              ,(case when `serializeMode` = 'serialize' then 'serialized' else `serializeMode` end)
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
              ,`hitNum`
              ,`recommended`
              ,`recommendedSeq`
              ,`recommendedTime`
          FROM `course` where `id` not in (select `id` from `course_v8`) order by id limit 0, {$this->perPageCount};";
        $result = $this->getConnection()->exec($sql);

        return $page + 1;
    }
}
