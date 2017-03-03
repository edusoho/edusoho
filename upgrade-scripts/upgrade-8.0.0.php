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
        $this->c2testpaperMigrate();
    }

    protected function c2courseSetMigrate()
    {
        if (!$this->isTableExist('c2_course_set')) {
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
            `id`
            ,`title`
            ,`subtitle`
            ,`status`
            ,`type`
            ,`serializeMode`
            ,`rating`
            ,`ratingNum`
            ,`categoryId`
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

        $sql = "UPDATE `c2_course_set` cs, `c2_course` c,
        SET cs.minCoursePrice = c.price, cs.maxCoursePrice = c.price where c.courseSetId = cs.id";
        $result = $this->getConnection()->exec($sql);
    }

    protected function c2testpaperMigrate()
    {
        $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `c2_testpaper` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `name` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
              `description` text COMMENT '试卷说明',
              `courseId` int(10) NOT NULL DEFAULT '0',
              `lessonId` int(10) NOT NULL DEFAULT '0',
              `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：秒)',
              `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷生成/显示模式',
              `target` varchar(255) NOT NULL DEFAULT '',
              `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状态：draft,open,closed',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
              `passedCondition` text,
              `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
              `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
              `metas` text COMMENT '题型排序',
              `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id',
              `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
              `courseSetId` int(11) unsigned NOT NULL DEFAULT '0',
              `oldTestId` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `c2_testpaper_item` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目',
              `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
              `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
              `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
              `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
              `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源testpaper_item的id',
              `oldItemId` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `c2_testpaper_result` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `paperName` varchar(255) NOT NULL DEFAULT '',
              `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'testId',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UserId',
              `courseId` int(10) NOT NULL DEFAULT '0',
              `lessonId` int(10) NOT NULL DEFAULT '0',
              `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
              `objectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
              `subjectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
              `teacherSay` text,
              `rightItemCount` int(10) unsigned NOT NULL DEFAULT '0',
              `passedStatus` enum('none','excellent','good','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '考试通过状态，none表示该考试没有',
              `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷限制时间(秒)',
              `beginTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
              `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
              `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `status` enum('doing','paused','reviewing','finished') NOT NULL COMMENT '状态',
              `target` varchar(255) NOT NULL DEFAULT '',
              `checkTeacherId` int(10) unsigned NOT NULL DEFAULT '0',
              `checkedTime` int(11) NOT NULL DEFAULT '0',
              `usedTime` int(10) unsigned NOT NULL DEFAULT '0',
              `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
              `courseSetId` int(11) unsigned NOT NULL DEFAULT '0',
              `oldResultId` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `c2_testpaper_item_result` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷题目id',
              `testId` int(10) unsigned NOT NULL DEFAULT '0',
              `resultId` int(10) NOT NULL DEFAULT '0' COMMENT '试卷结果ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `questionId` int(10) unsigned NOT NULL DEFAULT '0',
              `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none',
              `score` float(10,1) NOT NULL DEFAULT '0.0',
              `answer` text,
              `teacherSay` text,
              `pId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id',
              `oldItemResultId` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `testPaperResultId` (`resultId`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->testpaperUpgrade();
        $this->homeworkUpgrade();
        $this->exerciseUpdate();
        $this->testpaperActivity();
    }

    protected function testpaperUpgrade()
    {
        $sql        = "select * from testpaper";
        $testpapers = $this->getConnection()->fetchAll($sql);
        foreach ($testpapers as $testpaper) {
            $targetArr = explode('/', $testpaper['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonId  = 0;
            if (!empty($targetArr[1])) {
                $lessonArr = explode('-', $targetArr[1]);
                $lessonId  = $lessonArr[1];
            }
            $passedCondition = empty($testpaper['passedStatus']) ? '' : json_encode(array($testpaper['passedStatus']));

            $courseSql   = "select * from c2_course where oldCourseId = ".$courseArr[1];
            $newCourse   = $this->getConnection()->fetchAssoc($courseSql);
            $courseSetId = $newCourse['courseSetId'];

            $insertSql = "insert into c2_testpaper (id,name,description,courseId,lessonId,limitedTime,pattern,target,status,score,passedCondition,itemCount,createdUserId,createdTime,updatedUserId,updatedTime,metas,copyId,type,courseSetId,oldTestId) values({$testpaper['id']},'".$testpaper['name']."','".$testpaper['description']."',{$courseArr[1]},{$lessonId},{$testpaper['limitedTime']},'questionType','".$testpaper['target']."','".$testpaper['status']."',{$testpaper['score']},'".$passedCondition."',{$testpaper['itemCount']},{$testpaper['createdUserId']},{$testpaper['createdTime']},{$testpaper['updatedUserId']},{$testpaper['updatedTime']},'".$testpaper['metas']."',{$testpaper['copyId']},'testpaper',{$courseSetId},{$testpaper['id']})";
            $this->getConnection()->exec($insertSql);
        }

        //testpaper_item
        $sql = "insert into c2_testpaper_item (id,testId,seq,questionId,questionType,parentId,score,missScore,oldItemId) select id,testId,seq,questionId,questionType,parentId,score,missScore,id from testpaper_item";
        $this->getConnection()->exec($sql);

        //testpaper_result
        $sql = "insert into c2_testpaper_result(id,paperName,testId,userId,courseId,lessonId,score,objectiveScore,subjectiveScore,teacherSay,rightItemCount,passedStatus,limitedTime,beginTime,endTime,updateTime,active,status,target,checkTeacherId,checkedTime,usedTime,oldResultId) select (id,paperName,testId,userId,courseId,lessonId,score,objectiveScore,subjectiveScore,teacherSay,rightItemCount,passedStatus,limitedTime,beginTime,endTime,updateTime,active,status,target,checkTeacherId,checkedTime,usedTime,id) from c2_testpaper_result";
        $this->getConnection()->exec($sql);

        $sql                 = "select * from c2_testpaper_result";
        $newTestpaperResults = $this->getConnection()->fetchAll($sql);
        foreach ($newTestpaperResults as $testpaperResult) {
            $targetArr = explode('/', $testpaperResult['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonArr = explode('-', $targetArr[1]);

            $courseSql = "select * from c2_course where oldCourseId = ".$courseArr[1];
            $newCourse = $this->getConnection()->fetchAssoc($courseSql);

            $sql = "update c2_testpaper_result set courseId={$newCourse['id']},courseSetId={$newCourse['courseSetId']},type='testpaper' where id={$testpaperResult['id']}";

            $this->getConnection()->exec();
        }

        //testpaper_item_result
        $sql = "insert into c2_testpaper_item_result (id,itemId,testId,resultId,userId,questionId,status,score,answer,teacherSay,pId,oldItemResultId) select(id,itemId,testId,testPaperResultId,userId,questionId,status,score,answer,teacherSay,pId,id) from testpaper_item_result";
        $this->getConnection()->exec();

        $sql = "update c2_testpaper_item_result as ir set ir.testId = (select id from c2_testpaper where oldTestId = ir.testId)";
        $this->getConnection()->exec();

        $sql = "update c2_testpaper_item_result as ir set ir.resultId = (select id from c2_testpaper_result where oldResultId = ir.resultId)";
        $this->getConnection()->exec();

        //还需一个lessonId对应activityId
    }

    protected function homeworkUpgrade()
    {
        $sql    = "show tables like 'homework'";
        $result = $this->getConnection()->fetchAssoc($sql);
        if (!$result) {
            return;
        }

        $sql       = "select * from homework";
        $homeworks = $this->getConnection()->fetchAll($sql);
        if (!$homeworks) {
            return;
        }

        foreach ($homeworks as $homework) {
            $courseSql   = "select * from c2_course where oldCourseId = ".$courseArr[1];
            $newCourse   = $this->getConnection()->fetchAssoc($courseSql);
            $courseSetId = $newCourse['courseSetId'];

            $passedCondition = !empty($homework['correctPercent']) ? $homework['correctPercent'] : null;

            $insertSql = "insert into c2_testpaper (name,description,courseId,lessonId,limitedTime,pattern,target,status,score,passedCondition,itemCount,createdUserId,createdTime,updatedUserId,updatedTime,metas,copyId,type,courseSetId,oldTestId) values('','".$homework['description']."',{$homework['courseId']},{$homework['lessonId']},0,'questionType','','open',0,'".$passedCondition."',{$homework['itemCount']},{$homework['createdUserId']},{$homework['createdTime']},{$homework['updatedUserId']},{$homework['updatedTime']},null,{$homework['copyId']},'homework',{$courseSetId},{$homework['id']})";

            $this->getConnection()->exec($insertSql);
            $homeworkId  = $this->getConnection()->lastInsertId();
            $homeworkNew = $this->getConnection()->fetchAssoc("select * from c2_testpaper where id={$homeworkId}");

            if ($homework['copyId'] == 0) {
                $subSql = "update c2_testpaper set copyId = {$homeworkNew['id']} where copyId={$homework['id']} and type='homework'";
            }

            //homework_item
            $itemSql = "select * from homework_item where homeworkId={$homework['id']}";
            $items   = $this->getConnection()->fetchAll($itemSql);

            if (!$items) {
                continue;
            }

            foreach ($items as $item) {
                $sql = "insert into c2_testpaper_item (testId,seq,questionId,questionType,parentId,score,missScore,oldItemId) values({$homeworkNew['id']},{$item['seq']},{$item['questionId']},'".$item['questionType']."',{$item['parentId']},{$item['score']},{$item['missScore']},{$item['id']})";
                $this->getConnection()->exec($sql);
            }
        }

        //homework_result
        $sql     = "select * from homework_result";
        $results = $this->getConnection()->fetchAll($sql);

        foreach ($result as $result) {
            $courseSql = "select * from c2_course where oldCourseId={$result['courseId']}";
            $newCourse = $this->getConnection()->fetchAssoc($courseSql);
            //指向activityId
            $lessonId = $result['lessonId'];

            $homeworksql = "select * from c2_testpaper where oldTestId = {$result['homeworkId']}";
            $newHomework = $this->getConnection()->fetchAssoc($homeworksql);

            $sql = "insert into c2_testpaper_result(paperName,testId,userId,courseId,lessonId,score,objectiveScore,subjectiveScore,teacherSay,rightItemCount,passedStatus,limitedTime,beginTime,endTime,updateTime,active,status,target,checkTeacherId,checkedTime,usedTime,type,courseSetId,oldResultId) values('',{$newHomework['id']},{$result['userId']},{$newCourse['courseId']},{$lessonId},0,0,0,'".$result['teacherSay']."',{$result['rightItemCount']},{$result['passedStatus']},0,0,0,{$result['updatedTime']},0,{$result['status']},'',{$result['checkTeacherId']},{$result['checkedTime']},{$result['usedTime']},'homework',{$newCourse['courseSetId']},{$result['id']})";
            $this->getConnection()->exec($sql);
        }

        //homework_item_result
        $sql         = "select * from homework_item_result";
        $resultItems = $this->getConnection()->fetchAll($sql);

        foreach ($resultItems as $item) {
            $resultSql = "select * from c2_testpaper_result where oldResultId = {$item['homeworkResultId']}";
            $result    = $this->getConnection()->fetchAssoc($resultSql);

            $sql = "insert into c2_testpaper_item_result (itemId,testId,resultId,userId,questionId,status,score,answer,teacherSay,pId,oldItemResultId) values(0,{$result['testId']},{$result['id']},{$result['userId']},{$result['questionId']},{$result['status']},0,'".$result['answer']."','".$result['teacherSay']."',0,{$result['id']})";
            $this->getConnection()->exec($sql);
        }
    }

    protected function exerciseUpdate()
    {
        $sql    = "show tables like 'exercise'";
        $result = $this->getConnection()->fetchAssoc($sql);
        if (!$result) {
            return;
        }

        $sql       = "select * from exercise";
        $exercises = $this->getConnection()->fetchAll($sql);
        if (!$exercises) {
            return;
        }

        foreach ($exercises as $exercise) {
            $courseSql   = "select * from c2_course where oldCourseId = ".$courseArr[1];
            $newCourse   = $this->getConnection()->fetchAssoc($courseSql);
            $courseSetId = $newCourse['courseSetId'];

            $passedCondition = json_encode(array('type' => 'submit'));
            $metas           = null;
            if (!empty($exercise['difficulty'])) {
                $metas['difficulty'] = $exercise['difficulty'];
            }

            if (!empty($exercise['source'])) {
                $metas['range'] = $exercise['source'];
            }

            $metas['questionTypes'] = json_decode($exercise['questionTypeRange']);
            $metas                  = json_encode($metas);

            $insertSql = "insert into c2_testpaper (name,description,courseId,lessonId,limitedTime,pattern,target,status,score,passedCondition,itemCount,createdUserId,createdTime,updatedUserId,updatedTime,metas,copyId,type,courseSetId) values('','',{$exercise['courseId']},{$exercise['lessonId']},0,'questionType','','open',0,'".$passedCondition."',{$exercise['itemCount']},{$exercise['createdUserId']},{$exercise['createdTime']},0,0,'".$metas."',{$exercise['copyId']},'exercise',{$courseSetId})";

            $this->getConnection()->exec($insertSql);
            $exerciseId = $this->getConnection()->lastInsertId();

            $exerciseNew = $this->getConnection()->fetchAssoc("select * from c2_testpaper where id={$exerciseId}");

            if ($exercise['copyId'] == 0) {
                $subSql = "update c2_testpaper set copyId = {$exerciseNew['id']} where copyId={$exercise['id']} and type='exercise'";
            }

            //exercise_item
            $itemSql = "select * from exercise_item where exerciseId={$exercise['id']}";
            $items   = $this->getConnection()->fetchAll($itemSql);

            if (!$items) {
                continue;
            }

            foreach ($items as $item) {
                $sql = "insert into c2_testpaper_item (testId,seq,questionId,questionType,parentId,score,missScore,oldItemId) values({$exerciseNew['id']},{$item['seq']},{$item['questionId']},'',{$item['parentId']},{$item['score']},{$item['missScore']},{$item['id']})";
                $this->getConnection()->exec($sql);
            }
        }

        //homework_result
        $sql     = "select * from exercise_result";
        $results = $this->getConnection()->fetchAll($sql);

        foreach ($result as $result) {
            $courseSql = "select * from c2_course where oldCourseId={$result['courseId']}";
            $newCourse = $this->getConnection()->fetchAssoc($courseSql);
            //指向activityId
            $lessonId = $result['lessonId'];

            $exerciseSql = "select * from c2_testpaper where oldTestId = {$result['exerciseId']}";
            $newExercise = $this->getConnection()->fetchAssoc($exerciseSql);

            $sql = "insert into c2_testpaper_result(testId,userId,courseId,lessonId,rightItemCountupdateTime,status,usedTime,beginTime,type,courseSetId,oldResultId) values({$newExercise['id']},{$result['userId']},{$newCourse['courseId']},{$lessonId},{$result['rightItemCount']},{$result['updatedTime']},{$result['status']},{$result['usedTime']},{$result['createdTime']},'homework',{$newCourse['courseSetId']},{$result['id']})";
            $this->getConnection()->exec($sql);
        }

        //homework_item_result
        $sql         = "select * from exercise_item_result";
        $resultItems = $this->getConnection()->fetchAll($sql);

        foreach ($resultItems as $item) {
            $resultSql = "select * from c2_testpaper_result where oldResultId = {$item['exerciseResultId']}";
            $result    = $this->getConnection()->fetchAssoc($resultSql);

            $sql = "insert into c2_testpaper_item_result (itemId,testId,resultId,userId,questionId,status,score,answer,teacherSay,pId,oldItemResultId) values(0,{$result['testId']},{$result['id']},{$result['userId']},{$result['questionId']},{$result['status']},0,{$result['answer']},{$result['teacherSay']},0,{$result['id']})";
            $this->getConnection()->exec($sql);
        }
    }

    protected function testpaperActivity()
    {
        $sql = "insert into testpaper_activity (mediaId,limitedTime) select id,limitedTime from c2_testpaper where type='testpaper'";
        $this->getConnection()->exec($sql);

        $sql = "update testpaper_activity set checkType = 'score',finishCondition='{\"type\":\"submit\",\"finishScore\":\"0\"}'";
        $this->getConnection()->exec($sql);

        $sql = "update testpaper_activity as ta,(select ls.mediaId,ls.type,lse.doTimes,lse.redoInterval from course_lesson as ls right join course_lesson_extend as lse on ls.id=lse.id where ls.type='testpaper' and ls.mediaId >0) as tmp set ta.doTimes = tmp.doTimes,ta.redoInterval=tmp.redoInterval where tmp.mediaId = ta.mediaId";
        $this->getConnection()->exec($sql);
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
