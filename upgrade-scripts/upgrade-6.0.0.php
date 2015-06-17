<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

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
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();

        $connection->exec("UPDATE `course` c set income=(select sum(amount) from orders where targetId=c.id and targetType='course' and status in ('paid','refunding','refunded')) where id in (select distinct targetId from orders where targetType='course' and status in ('paid','refunding','refunded'));");

        if ($this->isTableExist('classroom')) {
            $connection->exec("UPDATE `classroom` c set income=(select sum(amount) from orders where targetId=c.id and targetType='classroom' and status in ('paid','refunding','refunded')) where id in (select distinct targetId from orders where targetType='classroom' and status in ('paid','refunding','refunded'));");
        }

        if (!$this->isFileGroupExist('block')) {
            $connection->exec("INSERT INTO `file_group` (`name`, `code`, `public`) VALUES ('编辑区', 'block', '1');");
        }

        $connection->exec("ALTER TABLE `crontab_job` CHANGE `cycle` `cycle` ENUM('once','everyhour','everyday','everymonth') NOT NULL DEFAULT 'once' COMMENT '任务执行周期';");

        if (!$this->isFieldExist('crontab_job', 'cycleTime')) {
            $connection->exec("ALTER TABLE `crontab_job` ADD `cycleTime` VARCHAR(255) NOT NULL DEFAULT '0' COMMENT '任务执行时间' AFTER `cycle`;");
        }

//todo install
        if (!$this->isCrontabJobExist('CancelOrderJob')) {
            $connection->exec("INSERT INTO `crontab_job` (`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('CancelOrderJob', 'everyhour', '0', 'Topxia\\Service\\Order\\Job\\CancelOrderJob', '', '0', '".time()."', '0', '0', '0');");
        }

        $this->updateArticle($connection);

//todo install
        $this->getSettingService()->set("crontab_next_executed_time", time());

        if(!$this->isFieldExist('thread', 'startTime')){
            $connection->exec("ALTER TABLE `thread` ADD `startTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开始时间' AFTER `status`;");
        }

        if(!$this->isFieldExist('thread', 'endTime')){
            $connection->exec("ALTER TABLE `thread` ADD `endTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结束时间' AFTER `startTIme`;");
        }

        if(!$this->isFieldExist('thread', 'maxUsers')){
            $connection->exec("ALTER TABLE `thread` ADD `maxUsers` INT(10) NOT NULL DEFAULT '0' COMMENT '最大人数' AFTER `hitNum`;");
        }

        if(!$this->isFieldExist('thread', 'location')){
            $connection->exec("ALTER TABLE `thread` ADD `location` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地点' AFTER `lastPostTime`;");
        }

        if(!$this->isFieldExist('thread', 'memberNum')){
            $connection->exec("ALTER TABLE `thread` ADD `memberNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '成员人数' AFTER `hitNum`;");
        }
        
        if(!$this->isTableExist('thread_member')) {
            $connection->exec("CREATE TABLE `thread_member` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统Id',
                `threadId` int(10) unsigned NOT NULL COMMENT '话题Id',
                `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
                `nickname` varchar(255) DEFAULT NULL COMMENT '昵称',
                `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
                `mobile` varchar(32) DEFAULT NULL COMMENT '手机号码',
                `createdTIme` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='话题成员表';
            ");
        }

        $connection->exec("ALTER TABLE `thread_member` CHANGE `createdTIme` `createdTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间';");

        if(!$this->isFieldExist('course', 'parentId')){
            $connection->exec("ALTER TABLE `course` ADD `parentId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程的父Id' AFTER `locationId`;");
        }

        if(!$this->isFieldExist('course_announcement', 'targetType')){
            $connection->exec("ALTER TABLE `course_announcement` ADD COLUMN `targetType` varchar(64) NOT NULL DEFAULT 'course' COMMENT '公告类型' AFTER `userId`");
        }

        if(!$this->isFieldExist('course_announcement', 'url')){
            $connection->exec("ALTER TABLE `course_announcement` ADD COLUMN `url` varchar(255) NOT NULL AFTER `targetType`");
        }

        if(!$this->isFieldExist('course_announcement', 'startTime')){
            $connection->exec("ALTER TABLE `course_announcement` ADD COLUMN `startTime` int(10) unsigned NOT NULL DEFAULT '0' AFTER `url`");
        }

        if(!$this->isFieldExist('course_announcement', 'endTime')){
            $connection->exec("ALTER TABLE `course_announcement` ADD COLUMN `endTime` int(10) unsigned NOT NULL DEFAULT '0' AFTER `startTime`");
        }

        if($this->isFieldExist('course_announcement', 'courseId')){
            $connection->exec("ALTER TABLE `course_announcement` CHANGE `courseId` `targetId`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告类型ID'");
        }

        if($this->isTableExist("announcement")) {

            if(!$this->isGlobalAnnouncementExist() && $this->isFieldExist('announcement', 'title')) {
                $connection->exec("insert into `course_announcement` 
                (content, url, startTime, endTime, userId, targetId, targetType, createdTime) 
                select title, url, startTime, endTime, userId, 0, 'global', 0 from announcement");
            }

            if(!$this->isTableExist("announcement_bak")) {
                $connection->exec("ALTER TABLE `announcement` RENAME TO `announcement_bak`;");
                $connection->exec("create table `announcement` like `course_announcement`;");
            }
        }

        if(!$this->isFieldExist('course_note', 'likeNum')){
            $connection->exec("ALTER TABLE `course_note` ADD `likeNum` INT(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞人数' AFTER `length`;");
        }

        if(!$this->isTableExist("course_note_like")) {
            $connection->exec("CREATE TABLE `course_note_like` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `noteId` int(11) NOT NULL,
                `userId` int(11) NOT NULL,
                `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

////done
        if(!$this->isFieldExist('status', 'courseId')){
            $connection->exec("ALTER TABLE `status` ADD `courseId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程Id' AFTER `userId`;");
        }

////done
        if(!$this->isFieldExist('status', 'classroomId')){
            $connection->exec("ALTER TABLE `status` ADD `classroomId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班级id' AFTER `courseId`;");
        }

////done
        if(!$this->isFieldExist('course_lesson', 'homeworkId')){
            $connection->exec("ALTER TABLE `course_lesson` ADD `homeworkId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '作业iD' AFTER `mediaUri`;");
        }
////done
        if(!$this->isFieldExist('course_lesson', 'exerciseId')){
            $connection->exec("ALTER TABLE `course_lesson` ADD `exerciseId` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT '练习ID' AFTER `homeworkId`;");
        }

        if(!$this->isFileGroupExist("classroom")){
            $connection->exec("INSERT INTO `file_group` (`id`, `name`, `code`, `public`) VALUES (NULL, '班级', 'classroom', '1');");
        }
////是否需要多个
        if(!$this->isFieldExist('thread', 'actvityPicture')){
            $connection->exec("ALTER TABLE `thread` ADD `actvityPicture` VARCHAR(255) NULL DEFAULT NULL COMMENT '活动图片' AFTER `maxUsers`;");
        }

        $connection->exec("
        CREATE TABLE IF NOT EXISTS `classroom` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL COMMENT '标题',
            `status` enum('closed','draft','published') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布',
            `about` text COMMENT '简介',
            `description` text COMMENT '课程说明',
            `price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
            `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支持的vip等级',
            `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
            `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
            `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
            `headTeacherId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班主任ID',
            `teacherIds` varchar(255) NOT NULL DEFAULT '' COMMENT '教师IDs',
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
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `classroom_courses` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `classroomId` int(10) unsigned NOT NULL COMMENT '班级ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $connection->exec("
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
              `role` enum('auditor','student','teacher','headTeacher','assistant', 'studentAssistant') NOT NULL DEFAULT 'auditor' COMMENT '角色',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ");

        $connection->exec("
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
        ");

        if (!$this->isFieldExist('classroom', 'recommended')) {
            $connection->exec("ALTER TABLE classroom ADD COLUMN `recommended` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐班级';");
        }

        if (!$this->isFieldExist('classroom', 'recommendedSeq')) {
            $connection->exec("ALTER TABLE classroom ADD COLUMN `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '推荐序号';");
        }

        if (!$this->isFieldExist('classroom', 'recommendedTime')) {
            $connection->exec("ALTER TABLE classroom ADD COLUMN `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间';");
        }

////done
        if (!$this->isFieldExist('course', 'noteNum')) {
            $connection->exec("ALTER TABLE `course` ADD `noteNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程笔记数量' AFTER `hitNum`;");
        }

        if (!$this->isFieldExist('classroom', 'rating')) {
            $connection->exec("
            ALTER TABLE `classroom` ADD `rating` FLOAT UNSIGNED NOT NULL DEFAULT '0' COMMENT '排行数值' AFTER `postNum`, ADD `ratingNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '投票人数' AFTER `rating`;
          ");
        }

        if (!$this->isFieldExist('classroom', 'categoryId')) {
            $connection->exec("ALTER TABLE `classroom` ADD `categoryId` INT(10) NOT NULL DEFAULT '0' COMMENT '分类id' AFTER `about`;");
        }

        $connection->exec("ALTER TABLE `classroom_member` CHANGE `role` `role` ENUM('auditor','student','teacher','headTeacher','assistant','studentAssistant') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'auditor' COMMENT '角色';");

        if (!$this->isFieldExist('classroom', 'private')) {
            $connection->exec("ALTER TABLE `classroom` ADD COLUMN `private` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否封闭班级';");
        }
        if (!$this->isFieldExist('classroom', 'service')) {
            $connection->exec("ALTER TABLE `classroom` ADD COLUMN `service` varchar(255) DEFAULT NULL COMMENT '班级服务';");
        }

        if (!$this->isFieldExist('classroom_courses', 'disabled')) {
            $connection->exec("ALTER TABLE `classroom_courses` ADD `disabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否禁用' AFTER `courseId`;");
        }
////done
        if (!$this->isFieldExist('classroom', 'noteNum')) {
            $connection->exec("ALTER TABLE `classroom` ADD `noteNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班级笔记数量' AFTER `threadNum`;");
        }
////done
        if (!$this->isFieldExist('classroom_courses', 'parentCourseId')) {
            $connection->exec("ALTER TABLE `classroom_courses` ADD `parentCourseId` INT(10) UNSIGNED NOT NULL COMMENT '父课程Id' AFTER `courseId`;");
        }

////done
        if (!$this->isFieldExist('thread', 'solved')) {
            $connection->exec("ALTER TABLE `thread` ADD `solved` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否有老师回答(已被解决)' AFTER `sticky`;");
        }

////done
        if (!$this->isFieldExist('thread_post', 'adopted')) {
            $connection->exec("ALTER TABLE `thread_post` ADD `adopted` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否被采纳(是老师回答)' AFTER `content`;");
        }

        if (!$this->isFieldExist('article', 'postNum')) {
            $connection->exec("ALTER TABLE `article` ADD `postNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复数' AFTER `sticky`;");
        }

        if (!$this->isFieldExist('article', 'upsNum')) {
            $connection->exec("ALTER TABLE `article` ADD `upsNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数' AFTER `postNum`;");
        }
        
        $connection->exec("CREATE TABLE IF NOT EXISTS `article_like` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
             `articleId` int(10) unsigned NOT NULL COMMENT '资讯id',
             `userId` int(10) unsigned NOT NULL COMMENT '用户id',
             `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞时间',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='资讯点赞表';");

        if (!$this->isFieldExist('classroom_courses', 'seq')) {
            $connection->exec("ALTER TABLE `classroom_courses` ADD `seq` INT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班级课程顺序' AFTER `parentCourseId`;");
        }

        if(!$this->isCrontabJobExist("DeleteSessionJob")){
        //    $connection->exec("INSERT INTO `crontab_job`(`name`, `cycle`, `cycleTime`, `jobClass`, `jobParams`, `executing`, `nextExcutedTime`, `latestExecutedTime`, `creatorId`, `createdTime`) VALUES ('DeleteSessionJob','everyhour',0,'Topxia\\Service\\User\\Job\\DeleteSessionJob','',0,".time().",0,0,0)");
        }

        ///删除classroom插件
        $connection->exec("DELETE from cloud_app where code='Classroom';");
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

    protected function isGlobalAnnouncementExist()
    {
        $sql = "select * from course_announcement where targetType='global';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isFileGroupExist($code)
    {
        $sql = "select * from file_group where code='{$code}'";
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
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    private function updateArticle($connection)
    {
        $sth = $connection->prepare("SELECT * FROM article");
        $sth->execute();
        while ($article = $sth->fetch(PDO::FETCH_ASSOC)) {
            $tagIds = json_decode($article['tagIds'], true);
            $tagIds = '|' . implode('|', $tagIds) . '|';
            $connection->exec("UPDATE article SET tagIds = '{$tagIds}' WHERE id = " . $article['id']);
        }
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
