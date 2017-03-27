<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System:SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System:SettingService')->set("crontab_next_executed_time", time());
    }

    protected function getStep($index)
    {
      $oldSteps = array(
        // 'c2CourseTaskMigrate',
        // 'c2Activity',
        // 'c2VideoActivity',
        // 'c2TextActivity',
        // 'c2AudioActivity',
        // 'c2FlashActivity',
        // 'c2PPtActivity',
        // 'c2DocActivity',
        // 'c2LiveActivity',

        'c2CourseTaskView',
        'c2CourseTaskResult',
        'c2ActivityLearnLog', // ?
        'c2Exercise',
        'c2Homework',
        'c2CourseMaterial',
        'c2TagOwner',
        'updateCourseChapter',
        'c2testpaperMigrate',
        'c2QuestionMigrate',
        'migrate',
      );

      $steps = array(
        'CourseSetMigrate',
        'CourseMigrate',
        
        'Lesson2CourseTaskMigrate',
        'Lesson2CourseChapterMigrate',
        'CourseTaskRelaCourseChapter',
        
        'Lesson2ActivityMigrate',
        'ActivityRelaCourseTask',

        'Lesson2VideoActivityMigrate',
        'ActivityRelaVideoActivity',

        'Lesson2TextActivityMigrate',
        'ActivityRelaTextActivity',

        'Lesson2AudioActivityMigrate',
        'ActivityRelaAudioActivity',

        'Lesson2FlashActivityMigrate',
        'ActivityRelaFlashActivity',

        'Lesson2PptActivityMigrate',
        'ActivityRelaPptActivity',

        'Lesson2DocActivityMigrate',
        'ActivityRelaDocActivity',

        'Lesson2LiveActivityMigrate',
        'ActivityRelaLiveActivity',
        // next


        'AfterAllCourseTaskMigrate',
        'OtherMigrate'
      );

      if ($index > count($steps)-1) {
         return '';
      }

      return $steps[$index];
    }

    protected function c2LiveActivity()
    {
        if (!$this->isTableExist('live_activity')) {
            $sql = "CREATE TABLE `live_activity` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `liveId` int(11) NOT NULL COMMENT '直播间ID',
              `liveProvider` int(11) NOT NULL COMMENT '直播供应商',
              `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态',
              `mediaId` INT(11) UNSIGNED DEFAULT 0 COMMENT '视频文件ID',
              `roomCreated` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '直播教室是否已创建',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $this->getConnection()->exec($sql);

            $sql = "INSERT INTO `live_activity` (
              `id`
              ,`liveId`
              ,`liveProvider`
              ,`replayStatus`
              ,`mediaId`
            ) SELECT
                `id`
                ,`mediaId`
                ,`liveProvider`
                ,`replayStatus`
                , 0
            FROM `course_lesson` where type='live' and `id` not in (select `id` from `live_activity`);";

            $result = $this->getConnection()->exec($sql);
        }

        $sql = 'UPDATE `live_activity` SET roomCreated = 1 WHERE liveId > 0;';
        $this->getConnection()->exec($sql);

        $sql = "UPDATE live_activity la, (SELECT globalId, lessonId FROM course_lesson_replay where globalId<>'' and globalId is not null) clr set la.mediaId = clr.lessonId WHERE la.id = clr.lessonId";
        $this->getConnection()->exec($sql);
    }

    protected function getIndexAndPage($index)
    {
      if($index == 0) {
        return array(0,1);
      }

      return explode('-', $index);
    }

    protected function setIndexAndPage($index, $page)
    {
      return "{$index}-{$page}";
    }

    protected function batchUpdate($index)
    { 
        $indexAndPage = $this->getIndexAndPage($index);
        $index = $indexAndPage[0];
        $page = 1;
        if (!empty($indexAndPage[1])) {
            $page = $indexAndPage[1];
        }

        $method = $this->getStep($index);
        if (empty($method)) {
          return;
        }

        require_once '8.0.0/AbstractMigrate.php';
        $file = "8.0.0/{$method}.php";
        require_once $file;
        $migrate = new $method($this->kernel);
        
        $this->logger('info', "开始迁移 {$method}");
        $nextPage = $migrate->update($page);
        $this->logger('info', "迁移 {$method} 成功");

        if (!empty($nextPage)) {
            return array(
                'index' => $this->setIndexAndPage($index, $nextPage),
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }

        return array(
            'index' => $this->setIndexAndPage($index+1, 1),
            'message' => '正在升级数据...',
            'progress' => 0
        );
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
                `teacherIds` varchar(1024) DEFAULT null,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

            $result = $this->getConnection()->exec($sql);
        }

        $sql = "INSERT INTO `c2_course_set` (
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
              ,`cover`
              ,`creator`
              ,`summary`
              ,`teacherIds`
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
              ,`teacherIds`
          FROM `course` where `id` not in (select `id` from `c2_course_set`);";

        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course_set` ce, (SELECT count(id) AS num , courseId FROM `course_material` GROUP BY courseId) cm  SET ce.`materialNum` = cm.num  WHERE ce.id = cm.`courseId`;";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `c2_course_set` cs, `course` c SET cs.minCoursePrice = c.price, cs.maxCoursePrice = c.price where cs.id = c.id";
        $result = $this->getConnection()->exec($sql);
    }

    protected function c2courseMigrate()
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
                  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
                  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
                  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
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
              ,`recommended`
              ,`recommendedSeq`
              ,`recommendedTime`
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
              ,`recommended`
              ,`recommendedSeq`
              ,`recommendedTime`
          FROM `course` where `id` not in (select `id` from `c2_course`);";
        $result = $this->getConnection()->exec($sql);

        // refactor: 直接course_set取
        $sql = "UPDATE `c2_course` ce, (SELECT count(id) AS num , courseId FROM `course_material` GROUP BY courseId) cm  SET ce.`materialNum` = cm.num  WHERE ce.id = cm.courseId;";
        $result = $this->getConnection()->exec($sql);

        // refactor: 这条语句不该在这个步骤中执行，应该在迁移完course_task后执行
        $sql = "UPDATE `c2_course` c set `publishedTaskNum` = (select count(*) from course_lesson where courseId=c.id and status = 'published')";
        $result = $this->getConnection()->exec($sql);
    }

    protected function updateCourseChapter()
    {
        $totalTasks = $this->getConnection()->fetchAll("SELECT * FROM `course_task`");

        $totalTasks = \AppBundle\Common\ArrayToolkit::group($totalTasks, 'lessonId');

        foreach ($totalTasks as $key => $tasks) {
            if (count($tasks) < 2) {
                continue;
            }
            foreach ($tasks as $task) {
                if ($task['mode'] == 'lesson') {
                    $categoryId = $task['categoryId'];
                    continue;
                }

                $this->getConnection()->update(
                    'course_task',
                    array('categoryId' => $categoryId),
                    array('id' => $task['id'])
                );

            }
        }
    }

    protected function c2TagOwner()
    {
        $this->exec(
            "update `tag_owner` set `ownerType` = 'courseSet' where `ownerType`='course';"
        );
    }

    protected function c2CourseMaterial()
    {

        if (!$this->isTableExist('download_activity')) {
            $this->exec(
                "
                CREATE TABLE `download_activity` (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                      `mediaCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料数',
                      `createdTime` int(10) unsigned NOT NULL,
                      `updatedTime` int(10) unsigned NOT NULL,
                      `fileIds` varchar(1024) DEFAULT NULL COMMENT '下载资料Ids',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('download_activity', 'lessonId')) {
            $this->exec("alter table `download_activity` add `lessonId` int(10) ;");
        }

        if (!$this->isFieldExist('course_material', 'courseSetId')) {
            $this->exec("alter table `course_material` add `courseSetId` int(10) ;");
        }
        $this->exec(" UPDATE `course_material` SET `courseSetId` = courseId;");
        $this->exec(" UPDATE `course_material` SET  `source`= 'courseactivity' WHERE source= 'courselesson';");


        //查找有复习资料的记录
        $downloadMaterials = $this->getConnection()->fetchAll(
            "SELECT *  FROM course_material WHERE source ='coursematerial' AND lessonid >0"
        );

        $downloadMaterials = \AppBundle\Common\ArrayToolkit::group($downloadMaterials, 'lessonId');

        //获取已经处理过的下载资料
        $downloadActivities = $this->getConnection()->fetchAll('select * from download_activity');
        $downloadActivities = \AppBundle\Common\ArrayToolkit::column($downloadActivities, 'lessonId');


        foreach ($downloadMaterials as $lessonId => $materials) {

            if (in_array($lessonId, $downloadActivities)) {
                continue;
            }

            //合并外链和本地资料
            array_filter(
                $materials,
                function (&$material) {
                    if (empty($material['fileId'])) {
                        $material['fileId'] = $material['link'];
                    }
                }
            );


            $fileCount = count($materials);
            $fileIds = \AppBundle\Common\ArrayToolkit::column($materials, 'fileId');
            $material = array_pop($materials);

            //download_activity
            $download = array(
                'mediaCount' => $fileCount,
                'createdTime' => $material['createdTime'],
                'updatedTime' => $material['createdTime'],
                'fileIds' => json_encode($fileIds),
                'lessonId' => $lessonId,
            );

            $this->getConnection()->insert('download_activity', $download);
            $downloadId = $this->getConnection()->lastInsertId();
            //activity
            $activity = array(
                'title' => '下载',
                'mediaId' => $downloadId,
                'mediaType' => 'download',
                'fromCourseId' => $material['courseId'],
                'fromCourseSetId' => $material['courseSetId'],
                'fromUserId' => $material['userId'],
                'createdTime' => $material['createdTime'],
                'updatedTime' => $material['createdTime'],

            );

            $this->getConnection()->insert('activity', $activity);
            $activityId = $this->getConnection()->lastInsertId();

            $lesson = $this->getConnection()->fetchAssoc("SELECT * FROM `course_lesson` WHERE id = {$lessonId}  ");
            //course_task
            $task = array(
                'courseId' => $lesson['courseId'],
                'seq' => $lesson['seq'],
                'categoryId' => $lesson['chapterId'],
                'activityId' => $activityId,
                'title' => '下载',
                'status' => $lesson['status'],
                'createdUserId' => $lesson['userId'],
                'createdTime' => $lesson['createdTime'],
                'updatedTime' => $lesson['updatedTime'],
                'mode' => 'extraClass',
                'number' => $lesson['number'],
                'type' => 'download',
                'lessonId' => $lessonId,
                'fromCourseSetId' => $lesson['courseId']
            );

            $this->getConnection()->insert('course_task', $task);
        }
    }

    /**
     * taskId 与lessonId一直
     */
    protected function c2CourseTaskMigrate()
    {
        if (!$this->isTableExist('course_task')) {
            $this->getConnection()->exec(
                "
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
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('course_task', 'lessonId')) {
            $this->exec("alter table `course_task` add `lessonId` int(10) ;");
        }

        if (!$this->isFieldExist('course_task', 'chapterId')) {
            $this->exec("alter table `course_task` add `chapterId` int(10) ;");
        }


        $this->getConnection()->exec(
            "
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
                 `copyId`,
                 `lessonId`
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
                CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
                `maxOnlineNum`,
                `copyId`,
                `id` as `lessonId`
            from `course_lesson` WHERE `id` NOT IN (SELECT id FROM `course_task`)
        "
        );

        $this->exec(
            "alter table `course_chapter` modify `type` varchar(255) NOT NULL DEFAULT 'chapter' COMMENT '章节类型：chapter为章节，unit为单元，lesson为课时。';"
        );

        if (!$this->isFieldExist('course_chapter', 'fromLessonId')) {
            $this->exec("alter table `course_chapter` add `fromLessonId` int(10) default 0;");
        }

        $sql = "insert into course_chapter (
          courseId,
          type,
          parentId,
          number,
          seq,
          title,
          createdTime,
          copyId,
          fromLessonId
        ) select 
          courseId,
          'lesson',
          chapterId,
          number,
          seq,
          title,
          createdTime,
          0,
          id
        from course_lesson where id not in (select fromLessonId from course_chapter where fromLessonId is not null and fromLessonId > 0 );";

        $this->exec($sql);

        $tasks = $this->getConnection()->fetchAll(
            "select * from `course_task` where mode = 'lesson' and chapterId IS  NULL;"
        );
        foreach ($tasks as $task) {
            $sql = 'select * from course_chapter where fromLessonId = ?';
            $chapter = $this->getConnection()->fetchAssoc($sql, array($task['lessonId']));
            if (empty($chapter)) {
                continue;
            }

            $task['categoryId'] = $chapter['id'];
            $fields = array('categoryId' => $chapter['id'], 'chapterId' => $chapter['id']);

            $this->getConnection()->update('course_task', $fields, array('id' => $task['id']));
        }
    }

    protected function c2Activity()
    {
        if (!$this->isTableExist('activity')) {
            $this->getConnection()->exec(
                "
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
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        $this->getConnection()->exec(
            "
            insert into `activity`(
                `id`,
                `title` ,
                `remark` ,
                `mediaId` ,
                `mediaType`,
                `content`,
                `length`,
                `fromCourseId`,
                `fromCourseSetId`,
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
                CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
                `courseId`,
                `courseId`,
                `userId`,
                `startTime`,
                `endTime`,
                `createdTime`,
                `updatedTime`,
                `copyId`
            from `course_lesson` where `id` not in (select id from `activity`);
        "
        );

        //update activityId in table course_task
        $this->getConnection()->exec(
            "UPDATE `course_task` ck, activity ay SET ck.`activityId` = ay.`id` WHERE ay.id = ck.`id` AND  ck.`activityId` = 0;"
        );
    }

    protected function c2VideoActivity()
    {
        if (!$this->isTableExist('video_activity')) {
            $this->getConnection()->exec(
                "
                CREATE TABLE `video_activity` (
                  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
                  `mediaId` int(10) NOT NULL DEFAULT 0 COMMENT '媒体文件ID',
                  `mediaUri` text COMMENT '媒体文件资UR',
                  `finishType` varchar(60) NOT NULL COMMENT '完成类型',
                  `finishDetail` text NOT NULL COMMENT '完成条件',
                   PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';
            "
            );
        }
        if (!$this->isFieldExist('video_activity', 'lessonId')) {
            $this->exec("alter table `video_activity` add `lessonId` int(10) ;");
        }
        $this->getConnection()->exec(
            "
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
        "
        );

        $this->getConnection()->exec(
            "
            UPDATE  `activity` AS ay ,`video_activity` AS vy   SET ay.`mediaId`  =  vy.id
            WHERE ay.id  = vy.lessonId   AND ay.`mediaType` = 'video';
        "
        );
    }

    protected function c2TextActivity()
    {
        if (!$this->isTableExist("text_activity")) {
            $this->getConnection()->exec(
                "
            CREATE TABLE `text_activity` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, time',
              `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
              `createdTime` int(10) NOT NULL,
              `createdUserId` int(11) NOT NULL,
              `updatedTime` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('text_activity', 'lessonId')) {
            $this->exec("alter table `text_activity` add `lessonId` int(10) ;");
        }

        $this->getConnection()->exec(
            "
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
        "
        );

        $this->getConnection()->exec(
            "
             UPDATE  `activity` AS ay ,`text_activity` AS ty   SET ay.`mediaId`  =  ty.id
             WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'text';
        "
        );
    }

    protected function c2AudioActivity()
    {
        if (!$this->isTableExist('audio_activity')) {
            $this->exec(
                "
                CREATE TABLE `audio_activity` (
                  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                  `mediaId` int(10) DEFAULT NULL COMMENT '媒体文件ID',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='音频活动扩展表';
            "
            );
        }

        if (!$this->isFieldExist('audio_activity', 'lessonId')) {
            $this->exec("alter table `audio_activity` add `lessonId` int(10) ;");
        }

        $this->exec(
            "
            insert into `audio_activity`
            (
                `mediaId`,
                `lessonId`
            )
            select
              `mediaId`,
              `id`
            from `course_lesson` where  type ='audio' and   `id` not in (select `lessonId` from `audio_activity`);
        "
        );

        $this->exec(
            "
          UPDATE  `activity` AS ay ,`audio_activity` AS ty   SET ay.`mediaId`  =  ty.id
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'audio';
         "
        );

        // $this->getConnection()->exec("alter table `audio_activity` add `lessonId` int(10) ;");
    }

    protected function c2FlashActivity()
    {
        if (!$this->isTableExist("flash_activity")) {
            $this->exec(
                "
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
            "
            );
        }

        if (!$this->isFieldExist('flash_activity', 'lessonId')) {
            $this->exec("alter table `flash_activity` add `lessonId` int(10) ;");
        }

        $this->exec(
            "
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
        "
        );

        $this->exec(
            "
          UPDATE  `activity` AS ay ,`flash_activity` AS ty   SET ay.`mediaId`  =  ty.id
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'flash';
         "
        );
    }

    protected function c2PPtActivity()
    {
        if (!$this->isTableExist('ppt_activity')) {
            $this->exec(
                "
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
            "
            );
        }

        if (!$this->isFieldExist('ppt_activity', 'lessonId')) {
            $this->exec("alter table `ppt_activity` add `lessonId` int(10) ;");
        }

        $this->exec(
            "
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
        "
        );

        $this->exec(
            "
          UPDATE  `activity` AS ay ,`ppt_activity` AS ty   SET ay.`mediaId`  =  ty.id
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'ppt';
         "
        );
    }

    protected function c2DocActivity()
    {
        if (!$this->isTableExist('doc_activity')) {
            $this->exec(
                "
                CREATE TABLE `doc_activity` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `mediaId` int(11) NOT NULL,
                  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, detail',
                  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
                  `createdTime` int(10) NOT NULL,
                  `createdUserId` int(11) NOT NULL,
                  `updatedTime` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('doc_activity', 'lessonId')) {
            $this->exec("alter table `doc_activity` add `lessonId` int(10) ;");
        }

        $this->exec(
            "
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
        "
        );

        $this->exec(
            "
          UPDATE  `activity` AS ay ,`doc_activity` AS ty   SET ay.`mediaId`  =  ty.id
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'doc';
         "
        );
    }

    protected function c2CourseTaskView()
    {
        if (!$this->isTableExist('course_task_view')) {
            $this->exec(
                "
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
            "
            );
        }

        $this->exec(
            "
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
                `courseId`,
                `courseId`,
                `lessonId`,
                `fileId`,
                `userId`,
                `fileType`,
                `fileStorage`,
                `fileSource`,
                `createdTime`
            FROM `course_lesson_view` WHERE id NOT IN (SELECT id FROM `course_task_view`);
        "
        );
    }

    protected function c2CourseTaskResult()
    {
        if (!$this->isTableExist('course_task_result')) {
            $this->exec(
                "
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
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        $this->exec(
            "
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
        "
        );

        $this->exec(
            "
            UPDATE `course_task_result` cl,  `course_task` ck SET cl.`activityId`= ck.`activityId` WHERE cl.`courseTaskId` = ck.`id`;
        "
        );
    }

    protected function c2ActivityLearnLog()
    {
        if (!$this->isTableExist('activity_learn_log')) {
            $this->exec(
                "
                CREATE TABLE `activity_learn_log` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',          
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `event` varchar(255) NOT NULL DEFAULT '' COMMENT '',
                  `data` text COMMENT '',
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `courseTaskId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `learnedTime` int(11) DEFAULT 0,
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        // $this->exec("
        //     insert into `activity_learn_log` (
        //       id
        //       ,activityId
        //       ,userId
        //       ,event
        //       ,data
        //       ,watchTime
        //       ,courseTaskId
        //       ,learnedTime
        //       ,createdTime
        //     ) values select 
        //       id
        //       ,lessonId
        //       ,userId
        //       ,learnedTime
        //     from course_lesson_learn where id not in (select id from activity_learn_log)
        // ");
    }

    /**
     * 将原来的练习转为activity 和 task
     */
    protected function c2Exercise()
    {
        if (!$this->isFieldExist('activity', 'exerciseId')) {
            $this->exec("alter table `activity` add `exerciseId` int(10) ;");
        }

        if (!$this->isFieldExist('course_task', 'exerciseId')) {
            $this->exec("alter table `course_task` add `exerciseId` int(10) ;");
        }

        //update activity
        $this->getConnection()->exec(
            "
            INSERT INTO `activity`
            (
                `title`,
                `remark` ,
                `mediaId` ,
                `mediaType`,
                `content`,
                `length`,
                `fromCourseId`,
                `fromCourseSetId`,
                `fromUserId`,
                `startTime`,
                `endTime`,
                `createdTime`,
                `updatedTime`,
                `copyId`,
                `exerciseId`
            )
            SELECT 
                '练习',
                `summary`,
                `eexerciseId`,
                'exercise',
                `summary`,
                CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
                `courseId`,
                `courseId`,
                `userId`,
                `startTime`,
                `endTime`,
                `createdTime`,
                `updatedTime`,
                `ecopyId`,
                `eexerciseId`
            FROM (SELECT  ee.id AS eexerciseId, ee.`copyId` AS ecopyId , ce.*  
            FROM  course_lesson  ce , exercise ee WHERE ce.id = ee.lessonid) lesson  
            WHERE lesson.eexerciseId NOT IN (SELECT exerciseId FROM activity WHERE exerciseId IS NOT NULL );
                    "
        );

        $this->exec(
            "
            insert into course_task
              (
                `courseId`,
                `fromCourseSetId`,
                `categoryId`,
                `seq`,
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
                `length` ,
                `maxOnlineNum`,
                `copyId`,
                `exerciseId`,
                `lessonId`
              ) 
            select
              `courseId`,
              `courseId`,
              `chapterId`,
              `seq`,
              '练习',
              `free`,
              `startTime`,
              `endTime`,
              `status`,
              `userId`,
              `createdTime`,
              `updatedTime`,
              'exercise',
              `number`,
              'exercise',
              CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
              `maxOnlineNum`,
              `copyId`,
              `eexerciseId`,
              `id`
              FROM (SELECT  ee.id AS eexerciseId, ee.`copyId` AS ecopyId , ce.*  
                FROM  course_lesson  ce , exercise ee WHERE ce.id = ee.lessonid) lesson  
                    WHERE lesson.eexerciseId NOT IN (SELECT exerciseId FROM course_task WHERE exerciseId IS NOT NULL );
            "
        );

        $this->exec(
            "UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id` 
          WHERE a.`exerciseId` = ck.`exerciseId` AND  ck.type = 'exercise' AND  ck.`activityId` = 0
            "
        );

    }


    /**
     * 将原来的练习转为activity 和 task
     */
    protected function c2Homework()
    {
        if (!$this->isFieldExist('activity', 'homeworkId')) {
            $this->exec("alter table `activity` add `homeworkId` int(10) ;");
        }

        if (!$this->isFieldExist('course_task', 'homeworkId')) {
            $this->exec("alter table `course_task` add `homeworkId` int(10) ;");
        }

        //update activity
        $this->getConnection()->exec(
            "
             INSERT INTO `activity`
            (
                `title`,
                `remark` ,
                `mediaId` ,
                `mediaType`,
                `content`,
                `length`,
                `fromCourseId`,
                `fromCourseSetId`,
                `fromUserId`,
                `startTime`,
                `endTime`,
                `createdTime`,
                `updatedTime`,
                `copyId`,
                `homeworkId`
            )
            SELECT 
                '作业',
                `summary`,
                `hhomeworkId`,
                'homework',
                `summary`,
                case when `length` is null then 0 else `length` end,
                `courseId`,
                `courseId`,
                `userId`,
                `startTime`,
                `endTime`,
                `createdTime`,
                `updatedTime`,
                `ecopyId`,
                `hhomeworkId`
            FROM (SELECT  ee.id AS hhomeworkId, ee.`copyId` AS ecopyId , ce.*  
            FROM  course_lesson  ce , homework ee WHERE ce.id = ee.lessonid) lesson  
            WHERE hhomeworkId NOT IN (SELECT homeworkId FROM activity WHERE homeworkId IS NOT NULL );
                    "
        );

        $this->exec(
            "
            INSERT INTO course_task
              (
                `courseId`,
                `fromCourseSetId`,
                `categoryId`,
                `seq`,
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
                `length` ,
                `maxOnlineNum`,
                `copyId`,
                `homeworkId`,
                `lessonId`
              ) 
            SELECT
              `courseId`,
              `courseId`,
              `chapterId`,
              `seq`,
              '作业',
              `free`,
              `startTime`,
              `endTime`,
              `status`,
              `userId`,
              `createdTime`,
              `updatedTime`,
              'homework',
              `number`,
              'homework',
              CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
              `maxOnlineNum`,
              `copyId`,
              `hhomeworkId`,
              `id`
              FROM (SELECT  ee.id AS hhomeworkId, ee.`copyId` AS ecopyId , ce.*  
                FROM  course_lesson  ce , homework ee WHERE ce.id = ee.lessonid) lesson  
                    WHERE lesson.hhomeworkId NOT IN (SELECT homeworkId FROM course_task WHERE homeworkId IS NOT NULL );
                    WHERE lesson.eexerciseId NOT IN (SELECT exerciseId FROM course_task WHERE exerciseId IS NOT NULL );
            "
        );

        $this->exec(
            "UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id` 
          WHERE a.`homeworkId` = ck.`homeworkId` AND  ck.type = 'homework' AND  ck.`activityId` = 0
            "
        );

    }

    protected function c2testpaperMigrate()
    {
        $this->getConnection()->exec(
            "
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
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
              `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
              `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
              PRIMARY KEY (`id`),
              KEY `testPaperResultId` (`resultId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `testpaper_activity` (
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
        "
        );

        $this->testpaperUpgrade();
        $this->homeworkUpgrade();
        $this->exerciseUpdate();
    }

    protected function testpaperUpgrade()
    {
        $sql = "SELECT * FROM testpaper WHERE id NOT IN (SELECT id FROM c2_testpaper WHERE type = 'testpaper')";
        $testpapers = $this->getConnection()->fetchAll($sql);
        foreach ($testpapers as $testpaper) {
            $targetArr = explode('/', $testpaper['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonId = 0;
            if (!empty($targetArr[1])) {
                $lessonArr = explode('-', $targetArr[1]);
                $lessonId = $lessonArr[1];
            }
            $passedCondition = empty($testpaper['passedStatus']) ? '' : json_encode(array($testpaper['passedStatus']));

            $courseSetId = $courseArr[1];

            $insertSql = "INSERT INTO c2_testpaper (
                id,
                name,
                description,
                courseId,
                lessonId,
                limitedTime,
                pattern,
                target,
                status,
                score,
                passedCondition,
                itemCount,
                createdUserId,
                createdTime,
                updatedUserId,
                updatedTime,
                metas,
                copyId,
                type,
                courseSetId,
                oldTestId
            ) VALUES (
                {$testpaper['id']},
                '".$testpaper['name']."',
                '".$testpaper['description']."',
                {$courseSetId},
                {$lessonId},
                {$testpaper['limitedTime']},
                'questionType',
                '".$testpaper['target']."',
                '".$testpaper['status']."',
                {$testpaper['score']},
                '".$passedCondition."',
                {$testpaper['itemCount']},
                {$testpaper['createdUserId']},
                {$testpaper['createdTime']},
                {$testpaper['updatedUserId']},
                {$testpaper['updatedTime']},
                '".$testpaper['metas']."',
                {$testpaper['copyId']},
                'testpaper',
                {$courseSetId},
                {$testpaper['id']}
                )";
            $this->getConnection()->exec($insertSql);
        }

        //testpaper_item
        $sql = "INSERT INTO c2_testpaper_item (
            id,
            testId,
            seq,
            questionId,
            questionType,
            parentId,
            score,
            missScore,
            oldItemId,
            type
        ) SELECT
            id,
            testId,
            seq,
            questionId,
            questionType,
            parentId,
            score,
            missScore,
            id,
            'testpaper' FROM testpaper_item
            WHERE id NOT IN (SELECT `id` FROM `c2_testpaper_item`)";
        $this->getConnection()->exec($sql);

        //testpaper_result
        $sql = "INSERT INTO c2_testpaper_result(
            id,
            paperName,
            testId,
            userId,
            courseId,
            lessonId,
            score,
            objectiveScore,
            subjectiveScore,
            teacherSay,
            rightItemCount,
            passedStatus,
            limitedTime,
            beginTime,
            endTime,
            updateTime,
            active,
            status,
            target,
            checkTeacherId,
            checkedTime,
            usedTime,
            oldResultId,
            type
        ) SELECT
            id,
            paperName,
            testId,
            userId,
            0,
            0,
            score,
            objectiveScore,
            subjectiveScore,
            teacherSay,
            rightItemCount,
            passedStatus,
            limitedTime,
            beginTime,
            endTime,
            updateTime,
            active,
            status,
            target,
            checkTeacherId,
            checkedTime,
            usedTime,
            id AS oldResultId,
            'testpaper'
            FROM testpaper_result WHERE id NOT IN (SELECT id FROM c2_testpaper_result)";
        $this->getConnection()->exec($sql);

        $sql = "SELECT * FROM c2_testpaper_result WHERE id NOT IN (SELECT id FROM c2_testpaper_result WHERE type = 'testpaper') and type = 'testpaper'";
        $newTestpaperResults = $this->getConnection()->fetchAll($sql);
        foreach ($newTestpaperResults as $testpaperResult) {
            $targetArr = explode('/', $testpaperResult['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonArr = explode('-', $targetArr[1]);

            $courseSql = "SELECT * FROM c2_course WHERE id = ".$courseArr[1];
            $course = $this->getConnection()->fetchAssoc($courseSql);

            $lessonId = empty($lessonArr[1]) ? 0 : $lessonArr[1];

            $sql = "UPDATE c2_testpaper_result SET
                courseId = {$course['id']},
                courseSetId = {$course['courseSetId']},
                lessonId = {$lessonId}
                WHERE id = {$testpaperResult['id']}";

            $this->getConnection()->exec($sql);
        }

        //testpaper_item_result
        $sql = "INSERT INTO c2_testpaper_item_result (
            id,
            itemId,
            testId,
            resultId,
            userId,
            questionId,
            status,
            score,
            answer,
            teacherSay,
            pId,
            oldItemResultId,
            type
        ) SELECT
            id,
            itemId,
            testId,
            testPaperResultId,
            userId,
            questionId,
            status,
            score,
            answer,
            teacherSay,
            pId,
            id,
            'testpaper'
            FROM testpaper_item_result WHERE id NOT IN (SELECT id FROM c2_testpaper_item_result)";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS ir, c2_testpaper as t SET ir.testId = t.id WHERE t.oldTestId = ir.testId";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS ir, c2_testpaper_result AS tr SET ir.resultId = tr.id WHERE tr.oldResultId = ir.resultId";
        $this->getConnection()->exec($sql);

        $this->testpaperActivity();

        $sql = "UPDATE c2_testpaper_result AS tr, course_task as ct SET tr.lessonId = ct.activityId WHERE tr.lessonId = ct.id AND tr.type='testpaper'";
        $this->exec($sql);
    }

    protected function homeworkUpgrade()
    {
        $sql = "show tables like 'homework'";
        $result = $this->getConnection()->fetchAssoc($sql);
        if (!$result) {
            return;
        }

        $sql = "SELECT * FROM homework WHERE id not IN (SELECT oldTestId FROM c2_testpaper WHERE type = 'homework')";
        $homeworks = $this->getConnection()->fetchAll($sql);
        if (!$homeworks) {
            return;
        }

        foreach ($homeworks as $homework) {
            $courseSetId = $homework['courseId'];

            $passedCondition = !empty($homework['correctPercent']) ? $homework['correctPercent'] : null;

            $insertSql = "INSERT INTO c2_testpaper (
                name,
                description,
                courseId,
                lessonId,
                limitedTime,
                pattern,
                target,
                status,
                score,
                passedCondition,
                itemCount,
                createdUserId,
                createdTime,
                updatedUserId,
                updatedTime,
                metas,
                copyId,
                type,
                courseSetId,
                oldTestId
            ) VALUES (
                '',
                '".$homework['description']."',
                {$homework['courseId']},
                {$homework['lessonId']},
                0,
                'questionType',
                '',
                'open',
                0,
                '".$passedCondition."',
                {$homework['itemCount']},
                {$homework['createdUserId']},
                {$homework['createdTime']},
                {$homework['updatedUserId']},
                {$homework['updatedTime']},
                null,
                {$homework['copyId']},
                'homework',
                {$courseSetId},
                {$homework['id']}
            )";

            $this->getConnection()->exec($insertSql);
            $homeworkId = $this->getConnection()->lastInsertId();
            $homeworkNew = $this->getConnection()->fetchAssoc("SELECT * FROM c2_testpaper WHERE id={$homeworkId}");

            if ($homework['copyId'] == 0) {
                $subSql = "UPDATE c2_testpaper SET copyId = {$homeworkNew['id']} WHERE copyId = {$homework['id']} AND type = 'homework'";
                $this->exec($subSql);
            }

            //homework_item
            $itemSql = "SELECT * FROM homework_item WHERE homeworkId = {$homework['id']} AND id NOT IN (SELECT oldItemId FROM c2_testpaper_item WHERE type = 'homework' AND testId = {$homework['id']})";
            $items = $this->getConnection()->fetchAll($itemSql);

            if (!$items) {
                continue;
            }

            foreach ($items as $item) {
                $sql = "INSERT INTO c2_testpaper_item (
                    testId,
                    seq,
                    questionId,
                    questionType,
                    parentId,
                    score,
                    missScore,
                    oldItemId
                ) VALUES (
                    {$homeworkNew['id']},
                    {$item['seq']},
                    {$item['questionId']},
                    '".$item['questionType']."',
                    {$item['parentId']},
                    {$item['score']},
                    {$item['missScore']},
                    {$item['id']}
                )";
                $this->getConnection()->exec($sql);
            }
        }

        $sql = "INSERT INTO c2_testpaper_result (
                paperName,
                testId,
                userId,
                courseId,
                lessonId,
                teacherSay,
                rightItemCount,
                passedStatus,
                updateTime,
                status,
                checkTeacherId,
                checkedTime,
                usedTime,
                type,
                courseSetId,
                oldResultId
            )SELECT
                '',
                homeworkId,
                userId,
                courseId,
                lessonId,
                teacherSay,
                rightItemCount,
                passedStatus,
                updatedTime,
                status,
                checkTeacherId,
                checkedTime,
                usedTime,
                'homework',
                courseId AS courseSetId,
                id AS oldResultId FROM homework_result WHERE id NOT IN (SELECT oldResultId FROM c2_testpaper_result WHERE type = 'homework')";
        $this->exec($sql);

        $sql = "UPDATE c2_testpaper_result AS tr,(SELECT id,oldTestId FROM c2_testpaper WHERE type ='homework') AS tmp SET testId = tmp.id WHERE tr.type = 'homework' AND tmp.oldTestId = tr.testId";
        $this->exec($sql);

        //需要与刘洋洋那边做好后，最终确认 lesson->activityId
        $sql = "UPDATE c2_testpaper_result AS tr,(SELECT id,mediaId FROM activity) AS tmp SET lessonId = tmp.Id WHERE tr.type = 'homework' AND tmp.mediaId = tr.testId";
        $this->exec($sql);

        $sql = "INSERT INTO c2_testpaper_item_result (
            testId,
            resultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            oldItemResultId,
            type
        ) SELECT
            homeworkId,
            homeworkResultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            id AS oldItemResultId,
            'homework'
            FROM homework_item_result WHERE id NOT IN (SELECT oldItemResultId FROM c2_testpaper_item_result where type = 'homework')";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS rt,(SELECT id,oldTestId FROM c2_testpaper WHERE type = 'homework') AS tmp SET rt.testId = tmp.id WHERE rt.type = 'homework' AND rt.testId = tmp.oldTestId;";
        $this->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS rt,(SELECT id,oldResultId FROM c2_testpaper_result WHERE type = 'homework') AS tmp SET rt.resultId = tmp.oldResultId WHERE rt.type = 'homework' AND rt.resultId = tmp.oldResultId;";
        $this->exec($sql);
    }

    protected function exerciseUpdate()
    {
        $sql = "show tables like 'exercise'";
        $result = $this->getConnection()->fetchAssoc($sql);
        if (!$result) {
            return;
        }

        $sql = "SELECT * FROM exercise WHERE id NOT IN (SELECT oldTestId FROM c2_testpaper WHERE type = 'exercise')";
        $exercises = $this->getConnection()->fetchAll($sql);
        if (!$exercises) {
            return;
        }

        foreach ($exercises as $exercise) {
            $courseSetId = $exercise['courseId'];

            $passedCondition = json_encode(array('type' => 'submit'));
            $metas = null;
            if (!empty($exercise['difficulty'])) {
                $metas['difficulty'] = $exercise['difficulty'];
            }

            if (!empty($exercise['source'])) {
                $metas['range'] = $exercise['source'];
            }

            $metas['questionTypes'] = json_decode($exercise['questionTypeRange']);
            $metas = json_encode($metas);

            $insertSql = "INSERT INTO c2_testpaper (
                name,
                description,
                courseId,
                lessonId,
                limitedTime,
                pattern,
                target,
                status,
                score,
                passedCondition,
                itemCount,
                createdUserId,
                createdTime,
                updatedUserId,
                updatedTime,
                metas,
                copyId,
                type,
                courseSetId,
                oldTestId
            ) VALUES (
                '',
                '',
                {$exercise['courseId']},
                {$exercise['lessonId']},
                0,
                'questionType',
                '',
                'open',
                0,
                '".$passedCondition."',
                {$exercise['itemCount']},
                {$exercise['createdUserId']},
                {$exercise['createdTime']},
                0,
                0,
                '".$metas."',
                {$exercise['copyId']},
                'exercise',
                {$courseSetId},
                {$exercise['id']}
            )";

            $this->getConnection()->exec($insertSql);
            $exerciseId = $this->getConnection()->lastInsertId();

            $exerciseNew = $this->getConnection()->fetchAssoc("SELECT * FROM c2_testpaper WHERE id={$exerciseId}");

            if ($exercise['copyId'] == 0) {
                $subSql = "UPDATE c2_testpaper SET copyId = {$exerciseNew['id']} WHERE copyId = {$exercise['id']} AND type = 'exercise'";
                $this->exec($subSql);
            }

            //exercise_item
            $itemSql = "SELECT * FROM exercise_item WHERE exerciseId = {$exercise['id']} AND id NOT IN (SELECT oldItemId FROM c2_testpaper_item WHERE type = 'exercise' AND testId = {$exercise['id']})";
            $items = $this->getConnection()->fetchAll($itemSql);

            if (!$items) {
                continue;
            }

            foreach ($items as $item) {
                $sql = "INSERT INTO c2_testpaper_item (
                    testId,
                    seq,
                    questionId,
                    questionType,
                    parentId,
                    score,
                    missScore,
                    oldItemId,
                    type
                ) values (
                    {$exerciseNew['id']},
                    {$item['seq']},
                    {$item['questionId']},
                    '',
                    {$item['parentId']},
                    {$item['score']},
                    {$item['missScore']},
                    {$item['id']},
                    'exercise'
                )";
                $this->getConnection()->exec($sql);
            }
        }

        /*$sql = "insert into c2_testpaper_item (testId,seq,questionId,parentId,score,missScore,oldItemId,type) select exerciseId,seq,questionId,parentId,score,missScore,id as oldItemId,'exercise' from exercise_item";
        $this->exec($sql);

        $sql = "update c2_testpaper_item as it set testId = (select id from c2_testpaper where oldTestId = it.testId and type = 'exercise') where type ='exercise'";
        $this->exec($sql);

        $sql = "update c2_testpaper_item as it set it.parentId = (select id from (select * from c2_testpaper_item) as tmp where tmp.oldItemId = it.parentId and tmp.type = 'exercise') where it.type ='exercise' and it.parentId > 0";
        $this->exec($sql);*/

        $sql = "INSERT INTO c2_testpaper_result (
                testId,
                userId,
                courseId,
                lessonId,
                rightItemCount,
                updateTime,
                status,
                usedTime,
                type,
                courseSetId,
                oldResultId )
            SELECT
                exerciseId,
                userId,
                courseId,
                lessonId,
                rightItemCount,
                updatedTime,
                status,
                usedTime,
                'exercise',
                0,
                id AS oldResultId
            FROM exercise_result WHERE id NOT IN (SELECT oldResultId FROM c2_testpaper_result WHERE type = 'exercise')";
        $this->exec($sql);

        //courseId,courseSetId 跟原来的值相同，只需要改testId和lessonId
        $sql = "UPDATE c2_testpaper_result AS tr, (SELECT id,oldTestId FROM c2_testpaper WHERE type = 'exercise') as tmp set testId = tmp.id where tr.type = 'exercise' AND tr.testId = tmp.id";
        $this->exec($sql);

        //需要与刘洋洋那边做好后，最终确认 lesson->activityId
        $sql = "UPDATE c2_testpaper_result AS tr, (SELECT id,mediaId FROM activity WHERE mediaType = 'exercise') as tmp set lessonId = tmp.id where tr.type = 'exercise' AND tr.testId = tmp.mediaId";
        $this->exec($sql);

        $sql = "INSERT INTO c2_testpaper_item_result (
            testId,
            resultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            oldItemResultId,
            type
        ) SELECT
            exerciseId,
            exerciseResultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            id AS oldItemResultId,
            'exercise' FROM exercise_item_result WHERE id NOT IN (SELECT oldItemResultId FROM c2_testpaper_item_result WHERE type = 'exercise')";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS rt,(SELECT id ,oldTestId FROM c2_testpaper WHERE type = 'exercise') AS tmp SET
            rt.testId = tmp.id WHERE rt.type = 'exercise' AND tmp.oldTestId = rt.testId ";
        $this->exec($sql);

        $sql = "UPDATE c2_testpaper_item_result AS rt,(SELECT id,oldResultId FROM c2_testpaper_result WHERE type = 'exercise') AS tmp SET
            rt.resultId = tmp.id WHERE rt.type = 'exercise' AND tmp.oldResultId = rt.resultId ";
        $this->exec($sql);
    }

    protected function testpaperActivity()
    {
        if (!$this->isFieldExist('testpaper_activity', 'lessonId')) {
            $this->exec("ALTER TABLE `testpaper_activity` add `lessonId` int(10) ;");
        }

        $sql = "INSERT INTO testpaper_activity (
            id,
            mediaId,
            checkType,
            finishCondition,
            requireCredit,
            doTimes,
            redoInterval
        )SELECT
            cl.id,
            cl.mediaId,
            'score',
            '{\"type\":\"submit\",\"finishScore\":\"0\"}',
            cl.requireCredit,
            case when cle.doTimes is null then 0 else cle.doTimes end as doTimes,
            case when cle.redoInterval is null then 0 else cle.redoInterval end as redoInterval
            FROM course_lesson AS cl
            LEFT JOIN
            course_lesson_extend AS cle
            ON cl.id=cle.id
            WHERE cl.type='testpaper' AND cl.id NOT IN (SELECT id FROM testpaper_activity)";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE testpaper_activity AS ta,(SELECT id,limitedTime,oldTestId FROM c2_testpaper) AS tmp SET ta.mediaId = tmp.id, ta.limitedTime = tmp.limitedTime WHERE tmp.oldTestId = ta.mediaId";
        $this->getConnection()->exec($sql);

        $this->exec(
            "
          UPDATE  `activity` AS ay ,`testpaper_activity` AS ty   SET ay.`mediaId`  =  ty.id
          WHERE ay.id  = ty.lessonId   AND ay.`mediaType` = 'testpaper';
         "
        );
    }

    protected function c2QuestionMigrate()
    {
        if (!$this->isFieldExist('question', 'courseId')) {
            $this->exec(
                "
                ALTER TABLE question add courseId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `target`
            "
            );
        }

        if (!$this->isFieldExist('question', 'courseSetId')) {
            $this->exec(
                "
                ALTER TABLE `question` ADD COLUMN `courseSetId` INT(10) NOT NULL DEFAULT '0'  AFTER `target`
            "
            );
        }

        if (!$this->isFieldExist('question', 'lessonId')) {
            $this->exec(
                "
                ALTER TABLE question add lessonId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `courseId`
            "
            );
        }

        $sql = "SELECT * FROM question";
        $questions = $this->getConnection()->fetchAll($sql);

        foreach ($questions as $question) {
            $targetArr = explode('/', $question['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonId = 0;
            if (!empty($targetArr[1])) {
                $lessonArr = explode('-', $targetArr[1]);
                $lessonId = $lessonArr[1];
            }

            $sql = "UPDATE question set courseId = {$courseArr[1]},courseSetId = {$courseArr[1]},lessonId={$lessonId} WHERE id = {$question['id']}";
            $this->exec($sql);
        }

        if (!$this->isFieldExist('question_favorite', 'targetType')) {
            $this->exec(
                "
                ALTER TABLE question_favorite ADD targetType VARCHAR(50) NOT NULL DEFAULT '' AFTER `questionId`
            "
            );
        }

        if (!$this->isFieldExist('question_favorite', 'targetId')) {
            $this->exec(
                "
                ALTER TABLE question_favorite ADD targetId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `targetType`
            "
            );
        }

        $sql = "SELECT * FROM question_favorite";
        $favorites = $this->getConnection()->fetchAll($sql);

        foreach ($favorites as $favorite) {
            $targetArr = explode('-', $favorite['target']);

            $sql = "UPDATE question_favorite set targetId = {$targetArr[1]},targetType='".$targetArr[0]."' WHERE id = {$favorite['id']}";
            $this->exec($sql);
        }
    }

    protected function migrate()
    {
        if ($this->isFieldExist('course_note', 'lessonId')) {
            $this->exec(
                "ALTER TABLE `course_note` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';"
            );
        }

        if (!$this->isFieldExist('course_note', 'courseSetId')) {
            $this->exec("ALTER TABLE `course_note` ADD COLUMN `courseSetId` INT(10) UNSIGNED NOT NULL;");
        }
        $this->exec("UPDATE course_note SET courseSetId = courseId");

        if (!$this->isFieldExist('course_review', 'courseSetId')) {
            $this->exec("ALTER TABLE `course_review` add COLUMN `courseSetId` int(10) UNSIGNED NOT NULL DEFAULT '0';");
        }
        $this->exec("UPDATE course_review SET courseSetId = courseId");

        if (!$this->isFieldExist('course_thread', 'courseSetId')) {
            $this->exec("ALTER TABLE `course_thread` ADD courseSetId INT(10) UNSIGNED NOT NULL;");
        }
        $this->exec("UPDATE course_thread SET courseSetId = courseId");

        if ($this->isFieldExist('course_thread', 'lessonId')) {
            $this->exec(
                "ALTER TABLE `course_thread` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';"
            );
        }

        if ($this->isFieldExist('course_thread_post', 'lessonId')) {
            $this->exec(
                "ALTER TABLE `course_thread_post` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';"
            );
        }

        if (!$this->isFieldExist('course_favorite', 'courseSetId')) {
            $this->exec('ALTER TABLE course_favorite ADD courseSetId INT(10) NOT NULL DEFAULT 0 COMMENT "课程ID";');
        }
        $this->exec("UPDATE course_favorite SET courseSetId = courseId");

        if ($this->isFieldExist('course_favorite', 'courseId')) {
            $this->exec("ALTER TABLE course_favorite MODIFY courseId INT(10) unsigned NOT NULL COMMENT '教学计划ID';");
        }

        if (!$this->isFieldExist('course_material', 'courseSetId')) {
            $this->exec("ALTER TABLE course_material ADD COLUMN courseSetId int(10) default 0 COMMENT '课程ID';");
        }
        $this->exec("UPDATE course_material SET courseSetId = courseId");

        if (!$this->isFieldExist('course_member', 'courseSetId')) {
            $this->exec(
                "ALTER TABLE `course_member` ADD COLUMN  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID';"
            );
        }
        $this->exec("UPDATE course_member SET courseSetId = courseId");

        if ($this->isFieldExist('course_member', 'courseId')) {
            $this->exec("ALTER TABLE `course_member` MODIFY courseId INT(10) unsigned NOT NULL COMMENT '教学计划ID';");
        }

        if (!$this->isFieldExist('classroom_courses', 'courseSetId')) {
            $this->exec(
                "ALTER TABLE `classroom_courses` ADD COLUMN `courseSetId` INT(10) NOT NULL DEFAULT '0' COMMENT '课程ID';"
            );
        }
        $this->exec("UPDATE classroom_courses SET courseSetId = courseId");

        $this->exec(
            'UPDATE course_member AS cm INNER JOIN c2_course c ON c.id = cm.courseId SET cm.courseSetId=c.courseSetId;'
        );
        $this->exec(
            "UPDATE block_template SET templateName = 'block/live-top-banner.template.html.twig' WHERE code = 'live_top_banner';"
        );
        $this->exec(
            "UPDATE block_template SET templateName = 'block/open-course-top-banner.template.html.twig' WHERE code = 'open_course_top_banner';"
        );

        $this->exec(
            "UPDATE `block_template` SET templateName = 'block/cloud-search-banner.template.html.twig' WHERE code = 'cloud_search_banner';"
        );

        $this->exec(
            "UPDATE crontab_job SET targetType = 'task' WHERE targetType = 'lesson' AND name = 'SmsSendOneDayJob';"
        );
        $this->exec(
            "UPDATE crontab_job SET targetType = 'task' WHERE targetType = 'lesson' AND name = 'SmsSendOneHourJob';"
        );


        $result = $this->getUserByType();

        if (empty($result)) {
            $this->exec("
                INSERT INTO `user` (`email`, `verifiedMobile`, `password`, `salt`, `payPassword`, `payPasswordSalt`, `locale`, `uri`, `nickname`, `title`, `tags`, `type`, `point`, `coin`, `smallAvatar`, `mediumAvatar`, `largeAvatar`, `emailVerified`, `setup`, `roles`, `promoted`, `promotedSeq`, `promotedTime`, `locked`, `lockDeadline`, `consecutivePasswordErrorTimes`, `lastPasswordFailTime`, `loginTime`, `loginIp`, `loginSessionId`, `approvalTime`, `approvalStatus`, `newMessageNum`, `newNotificationNum`, `createdIp`, `createdTime`, `updatedTime`, `inviteCode`, `orgId`, `orgCode`, `registeredWay`) VALUES
    ('user_tfo2ex19h@edusoho.net', '', '3DMYb8GyEXk32ruFzw4lxy2elz6/aoPtA5X8vCTWezg=', 'qunt972ow5c48k4wc8k0ss448os0oko', '', '', NULL, '', 'user70rbkm(系统用户)', '', '', 'scheduler', 0, 0, '', '', '', 1, 1, '|ROLE_USER|ROLE_SUPER_ADMIN|', 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 'unapprove', 0, 0, '', 1489204100, 1489204100, NULL, 1, '1.', '');
            ");

            $result = $this->getUserByType();

            $sql = "INSERT INTO `user_profile` (`id`, `truename`, `idcard`, `gender`, `iam`, `birthday`, `city`, `mobile`, `qq`, `signature`, `about`, `company`, `job`, `school`, `class`, `weibo`, `weixin`, `isQQPublic`, `isWeixinPublic`, `isWeiboPublic`, `site`, `intField1`, `intField2`, `intField3`, `intField4`, `intField5`, `dateField1`, `dateField2`, `dateField3`, `dateField4`, `dateField5`, `floatField1`, `floatField2`, `floatField3`, `floatField4`, `floatField5`, `varcharField1`, `varcharField2`, `varcharField3`, `varcharField4`, `varcharField5`, `varcharField6`, `varcharField7`, `varcharField8`, `varcharField9`, `varcharField10`, `textField1`, `textField2`, `textField3`, `textField4`, `textField5`, `textField6`, `textField7`, `textField8`, `textField9`, `textField10`) VALUES
    ({$result['id']}, '', '', 'secret', '', NULL, '', '', '', NULL, NULL, '', '', '', '', '', '', 0, 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";

            $this->exec($sql);
        }

        if ($this->isFieldExist('course_draft', 'lessonId')) {
          $this->exec("ALTER TABLE course_draft CHANGE lessonId activityId INT(10) unsigned NOT NULL COMMENT '教学活动ID';");
        }
    }

    private function getUserByType()
    {
        $sql = "select * from user where type='scheduler' limit 1;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return $result;
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param  string $statement
     * @throws \Doctrine\DBAL\DBALException
     * @return integer                        The number of affected rows.
     */
    protected function exec($statement)
    {
        return $this->getConnection()->exec($statement);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    protected function getCourseChapterDao()
    {
        return ServiceKernel::instance()->getBiz()->dao('Course:CourseChapterDao'); 
    }

    protected function logger($level, $message)
    {
        $data = date("Y-m-d H:i:s").' ['.$level.'] 6.17.9 '.$message.PHP_EOL;
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    protected function getLoggerFile()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/logs/upgrade.log";
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
