<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try{
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
        } catch(\Exception $e) {
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

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist('classroom', 'updatedTime')) {
            $connection->exec("ALTER TABLE `classroom` ADD `updatedTime`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `createdTime`;");
        }

        if (!$this->isFieldExist('orders', 'updatedTime')) {
            $connection->exec("ALTER TABLE  `orders` ADD  `updatedTime` INT(10) NOT NULL AFTER  `createdTime`; ");
        }

        if (!$this->isFieldExist('course_member', 'lastLearnTime')) {
            $connection->exec(" ALTER TABLE `course_member` ADD `lastLearnTime` INT(10) COMMENT '最后学习时间';");
        }

        if (!$this->isFieldExist('classroom_member', 'lastLearnTime')) {
            $connection->exec(" ALTER TABLE `classroom_member` ADD `lastLearnTime` INT(10) COMMENT '最后学习时间';");
        }

        if (!$this->isFieldExist('classroom_member', 'learnedNum')) {
            $connection->exec(" ALTER TABLE `classroom_member` ADD `learnedNum` INT(10) COMMENT '已学课时数'; ");
        }

        if (!$this->isFieldExist('course_member', 'updatedTime')) {
            $connection->exec("ALTER TABLE `course_member` ADD `updatedTime` INT(10) NOT NULL DEFAULT '0'COMMENT '最后更新时间'");
        }

        if (!$this->isFieldExist('classroom_member', 'updatedTime')) {
            $connection->exec("ALTER TABLE `classroom_member` ADD `updatedTime` INT(10) NOT NULL DEFAULT'0' COMMENT '最后更新时间' ");
        }

        if (!$this->isFieldExist('course_lesson_replay', 'globalId')) {
            $connection->exec("ALTER TABLE `course_lesson_replay` ADD `globalId` CHAR(32) NOT NULL DEFAULT '' COMMENT '云资源ID' AFTER `replayId` ");
        }
        
        $connection->exec("
            CREATE TABLE  IF NOT EXISTS `ratelimit` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `_key` varchar(64) NOT NULL,
                `data` varchar(32) NOT NULL,
                `deadline` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `_key` (`_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function batchUpdate($index)
    {
        $batchUpdates = array(
            1 => 'updateClassroomUpdateTime',
            2 => 'updateOrdersUpdateTimeFromLog',
            3 => 'updateOrdersUpdateTime',
            4 => 'updateCourseMemberLastLearnTime',
            5 => 'updateClassroomMemberLastLearnTime',
            6 => 'updateClassroomMemberLastLearnedNum',
            7 => 'updateCourseMemberUpdatedTime',
            8 => 'updateClassroomMemberUpdatedTime',
        );
        if ($index == 0) {
            $this->updateScheme();

            return array(
                'index' => '1-1',
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }
        $step = preg_replace('/\-\w+$/', '', $index);
        $page = preg_replace('/^\w+\-/', '', $index);

        $method = $batchUpdates[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            $step ++;
        }
        if ($step < 8) {
            return array(
                'index' => $step.'-'.$page,
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }
    }

    protected function updateClassroomUpdateTime()
    {
        $connection = $this->getConnection();

        $connection->exec("UPDATE `classroom` set `updatedTime`= createdTime WHERE updatedTime = 0 ; ");

        return 1;
    }

    protected function updateOrdersUpdateTimeFromLog($page = 1)
    {
        $connection = $this->getConnection();

        $count = $connection->fetchColumn("select count(*) from orders;");
        $pageNum = 1000;
        $pages = intval(floor($count/$pageNum)) + ($count%$pageNum>0 ? 1 : 0);

        if ($page <= $pages) {
            $start = ($page-1) * $pageNum;

            $ids = $connection->fetchAll("select id from orders order by id limit {$start},{$pageNum}");
            if (!empty($ids)) {
                $ids = ArrayToolkit::column($ids, 'id');
                $ids = implode(',', $ids);
                $connection->exec("UPDATE `orders` SET `updatedTime` = (select ifnull(max(createdTime),0) from `order_log` where order_log.orderId = orders.id) where id in ({$ids});");
            }
            if ($page < $pages) {
                return ++$page;
            }
        }
        return 1;
    }

    protected function updateOrdersUpdateTime()
    {
        $connection = $this->getConnection();

        $connection->exec("UPDATE `orders` set `updatedTime`= createdTime WHERE updatedTime = 0 ;");

        return 1;
    }

    protected function updateCourseMemberLastLearnTime($page = 1)
    {
        $connection = $this->getConnection();

        $count = $connection->fetchColumn("select count(*) from course_member;");
        $pageNum = 1000;
        $pages = intval(floor($count/$pageNum)) + ($count%$pageNum>0 ? 1 : 0);

        if ($page <= $pages) {
            $start = ($page-1) * $pageNum;

            $ids = $connection->fetchAll("select id from course_member order by id limit {$start},{$pageNum}");
            if (!empty($ids)) {
                $ids = ArrayToolkit::column($ids, 'id');
                $ids = implode(',', $ids);
                $connection->exec(" UPDATE `course_member` SET `lastLearnTime` = (SELECT ifnull(max(startTime),0) FROM `course_lesson_learn` WHERE course_member.courseId = course_lesson_learn.courseId AND course_member.userId = course_lesson_learn.userId) where id in ({$ids});");
            }
            if ($page < $pages) {
                return ++$page;
            }
        }

        return 1;
    }

    protected function updateClassroomMemberLastLearnTime($page = 1)
    {
        $connection = $this->getConnection();

        $count = $connection->fetchColumn("select count(*) from course_member where joinedType = 'classroom';");
        $pageNum = 1000;
        $pages = intval(floor($count/$pageNum)) + ($count%$pageNum>0 ? 1 : 0);

        if ($page <= $pages) {
            $start = ($page-1) * $pageNum;

            $ids = $connection->fetchAll("select id from course_member where joinedType = 'classroom' order by id limit {$start},{$pageNum}");

            if (!empty($ids)) {
                $ids = ArrayToolkit::column($ids, 'id');
                $ids = implode(',', $ids);
                $connection->exec(" UPDATE `classroom_member` SET `lastLearnTime` = (SELECT ifnull(max(lastLearnTime),0) FROM `course_member` WHERE classroom_member.classroomId = course_member.classroomId AND classroom_member.userId = course_member.userId AND course_member.joinedType = 'classroom') where id in ({$ids});");
            }
            if ($page < $pages) {
                return ++$page;
            }
        }

        return 1;
    }

    protected function updateClassroomMemberLastLearnedNum($page = 1)
    {
        $connection = $this->getConnection();

        $count = $connection->fetchColumn("select count(*) from course_member where joinedType = 'classroom';");
        $pageNum = 1000;
        $pages = intval(floor($count/$pageNum)) + ($count%$pageNum>0 ? 1 : 0);

        if ($page <= $pages) {
            $start = ($page-1) * $pageNum;

            $ids = $connection->fetchAll("select id from course_member where joinedType = 'classroom' order by id limit {$start},{$pageNum}");

            if (!empty($ids)) {
                $ids = ArrayToolkit::column($ids, 'id');
                $ids = implode(',', $ids);
                $connection->exec(" UPDATE `classroom_member` SET `learnedNum` = (SELECT ifnull(sum(learnedNum),0) FROM `course_member` WHERE classroom_member.classroomId = course_member.classroomId AND classroom_member.userId = course_member.userId AND course_member.joinedType = 'classroom') where id in ({$ids});");
            }

            if ($page < $pages) {
                return ++$page;
            }
        }

        return 1;
    }

    protected function updateCourseMemberUpdatedTime()
    {
        $connection = $this->getConnection();

        $connection->exec("UPDATE `course_member` SET `updatedTime` = `createdTime`");

        return 1;
    }

    protected function updateClassroomMemberUpdatedTime()
    {
        $connection = $this->getConnection();

        $connection->exec("UPDATE  `classroom_member` SET `updatedTime` = `createdTime`");

        return 1;
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct ($kernel)
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