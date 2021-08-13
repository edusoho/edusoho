<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;
use Symfony\Component\Yaml\Yaml;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
         $this->getConnection()->beginTransaction();
         try {
             $this->updateScheme();
             $this->getConnection()->commit();

             $this->updateCrontabSetting();
         } catch (\Exception $e) {
             $this->getConnection()->rollback();
             throw $e;
         }

        try {
             $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
             $filesystem = new Filesystem();

             if (!empty($dir)) {
                 $filesystem->remove($dir);
             }
        } catch (\Exception $e) {
        }

         $developerSetting = $this->getSettingService()->get('developer', array());
         $developerSetting['debug'] = 0;

         ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
         ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());

    }

    private function updateScheme()
    {
        $connection = $this->getConnection();
        
        if (!$this->isTableExist('task')){
            $connection->exec("
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
                  `status` enum('active','completed') NOT NULL DEFAULT 'active',
                  `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为必做任务,0否,1是',
                  `completedTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务完成时间',
                  `createdTime` int(10) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('batch_notification')){
            $connection->exec("
                CREATE TABLE `batch_notification` (
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
            ");
        }

        if (!$this->isFieldExist('crontab_job', 'targetType')) {
            $connection->exec("ALTER TABLE  `crontab_job` ADD  `targetType` VARCHAR( 64 ) NOT NULL DEFAULT  '' AFTER  `jobParams`");
        }
        if (!$this->isFieldExist('crontab_job', 'targetId')) {
            $connection->exec("ALTER TABLE  `crontab_job` ADD  `targetId` INT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `targetType`");
        }
        
        if (!$this->isFieldExist('notification', 'batchId')) {
            $connection->exec("ALTER TABLE `notification` ADD `batchId` int(10) NOT NULL DEFAULT '0' COMMENT '群发通知表中的ID' AFTER `content`; ");
        }

        if ($this->isFieldExist('course_lesson', 'parentId')) {
            $connection->exec("ALTER TABLE `course_lesson` CHANGE `parentId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制课时id'");
        }

        if (!$this->isFieldExist('course_lesson', 'maxOnlineNum')) {
            $connection->exec("ALTER TABLE `course_lesson` ADD `maxOnlineNum` INT NULL DEFAULT '0' COMMENT '直播在线人数峰值' AFTER `replayStatus`");
        }

        if ($this->isFieldExist('question', 'pId')) {
            $connection->exec("ALTER TABLE `question` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制问题对应Id'");
        }

        if ($this->isFieldExist('testpaper', 'pId')) {
            $connection->exec("ALTER TABLE `testpaper` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id'");
        }

        if ($this->isFieldExist('testpaper_item', 'pId')) {
            $connection->exec("ALTER TABLE `testpaper_item` DROP `pId`");
        }

        if ($this->isFieldExist('course_material', 'pId')) {
            $connection->exec("ALTER TABLE `course_material` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id'");
        }

        if ($this->isFieldExist('course_chapter', 'pId')) {
            $connection->exec("ALTER TABLE `course_chapter` CHANGE `pId` `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制章节的id'");  
        }

        $developerSetting = $this->getSettingService()->get('refund', array());
        $developerSetting['applyNotification'] = '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。';
        $developerSetting['successNotification'] = '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。';
        $developerSetting['failedNotification'] = '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。';
        $this->getSettingService()->set('refund', $developerSetting);

        $connection->exec("update block set title='简墨主题：首页顶部.轮播图' where code='jianmo:home_top_banner'");
        $connection->exec("update block set title='简墨主题: 首页底部.链接区域' where code='jianmo:bottom_info'");
        $connection->exec("update block set title='简墨主题：首页中部.横幅' where code='jianmo:middle_banner'");

        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms', array());
        if(isset($cloudSmsSetting['sms_enabled']) && $cloudSmsSetting['sms_enabled']) {
            $cloudSmsSetting['sms_user_pay'] = '1';
            $cloudSmsSetting['sms_bind'] = 'on';
            $cloudSmsSetting['sms_forget_password'] = 'on';
            $cloudSmsSetting['sms_forget_pay_password'] = 'on';
            $cloudSmsSetting['sms_user_pay'] = 'on';
            $this->getSettingService()->set('cloud_sms', $cloudSmsSetting);
        }

    }

    private function updateCrontabSetting()
    {
        $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/data/crontab_config.yml");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
        }
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
