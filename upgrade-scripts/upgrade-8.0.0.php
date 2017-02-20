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
    }

    protected function c2courseSetMigrate()
    {
        if(!$this->isTableExist('c2_course_set')) {
            $sql = "CREATE TABLE `c2_course_set` (
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

        $sql = "INSERT INTO `c2_course_set` (
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

        $sql = "UPDATE `c2_course_set` AS `c` SET `c`.`parentId` =  (select `id` from `c2_course_set` where `oldCourseId` = `c`.`oldCourseId`)";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course_set` ce, (SELECT count(id) AS num , courseSetId FROM `course_material` GROUP BY courseSetId) cm  SET ce.`materialNum` = cm.num  WHERE ce.id = cm.`courseSetId`;";
        $result = $this->getConnection()->exec($sql);
    }

    protected function c2courseMigrate()
    {
        if(!$this->isTableExist('c2_course')) {
            $sql = "CREATE TABLE `c2_course` (
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
                  `publishedTaskNum` INT(10) DEFAULT '0' COMMENT '已发布的任务数' AFTER `taskNum`,
                  `oldCourseId` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

            $result = $this->getConnection()->exec($sql);
        }

        $sql = "INSERT INTO `c2_course` (
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

        $sql = "UPDATE `c2_course` AS `c` SET `c`.`courseSetId` =  (select `id` from `c2_course_set` where `oldCourseId` = `c`.`oldCourseId`)";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course` AS `c` SET `c`.`parentId` =  (select `id` from `c2_course` where `oldCourseId` = `c`.`oldCourseId`)";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course` AS `c` SET `c`.`copyCourseId` = `c`.`parentId`";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course` AS `c` SET `c`.`cloneId` = `c`.`parentId`";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course` ce, (SELECT count(id) AS num , courseId FROM `course_material` GROUP BY courseId) cm  SET ce.`materialNum` = cm.num  WHERE ce.id = cm.courseId;";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course_set` cs, `c2_course` c, 
        SET cs.minCoursePrice = c.price, cs.maxCoursePrice = c.price where c.courseSetId = cs.id";
        $result = $this->getConnection()->exec($sql);        
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
