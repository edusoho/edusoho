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
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='群发通知表';
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

        //$jianmoMeta1 = '{"title":"\\u7b80\\u58a8\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u9876\\u90e8\\u002e\\u8f6e\\u64ad\\u56fe\\u0020 ","category":"jianmo","templateName":"@theme\/jianmo\/block\/carousel.template.html.twig","items":{"posters":{"title":"\u6d77\u62a5","desc":"\u9996\u9875\u6d77\u62a5","count":1,"type":"poster","default":[{"src":"\/themes\/jianmo\/img\/banner_net.jpg","alt":"\\u6d77\\u62a51","layout":"tile","background":"#3ec768","href":"","html":"","status":"1","mode":"img"},{"src":"\/themes\/jianmo\/img\/banner_app.jpg","alt":"\u6d77\u62a52","layout":"tile","background":"#0984f7","href":"","html":"","status":"1","mode":"img"},{"src":"\/themes\/jianmo\/img\/banner_eweek.jpg","alt":"\u6d77\u62a53","layout":"tile","background":"#3b4250","href":"","html":"","status":"1","mode":"img"},{"src":"\/themes\/jianmo\/img\/banner_net.jpg","alt":"\u6d77\u62a54","layout":"tile","background":"#3ec768","href":"","html":"","status":"0","mode":"img"},{"src":"\/themes\/jianmo\/img\/banner_net.jpg","alt":"\u6d77\u62a55","layout":"tile","background":"#3ec768","href":"","html":"","status":"0","mode":"img"},{"src":"\/themes\/jianmo\/img\/banner_net.jpg","alt":"\u6d77\u62a56","layout":"tile","background":"#3ec768","href":"","html":"","status":"0","mode":"img"},{"src":"\/themes\/jianmo\/img\/banner_net.jpg","alt":"\u6d77\u62a57","layout":"tile","background":"#3ec768","href":"","html":"","status":"0","mode":"img"},{"src":"\/themes\/jianmo\/img\/banner_net.jpg","alt":"\u6d77\u62a58","layout":"tile","background":"#3ec768","href":"","html":"","status":"0","mode":"img"}]}}}';
        $connection->exec('update block set title="简墨主题：首页顶部.轮播图 ",meta="{$jianmoMeta1}" where code="jianmo:home_top_banner"');
        //$jianmoMeta3 = '{"title":"\\u7b80\\u58a8\\u4e3b\\u9898\\u003a\\u0020\\u9996\\u9875\\u5e95\u90e8\\u002e\\u94fe\\u63a5\\u533a\\u57df","category":"jianmo","templateName":"@theme\/jianmo\/block\/bottom_info.template.html.twig","items":{"firstColumnText":{"title":"\u7b2c\uff11\u5217\u94fe\u63a5\u6807\u9898","desc":"","count":1,"type":"text","default":[{"value":"\u6211\u662f\u5b66\u751f"}]},"firstColumnLinks":{"title":"\\u7b2c\\uff11\\u5217\\u94fe\u63a5","desc":"","count":5,"type":"link","default":[{"value":"\u5982\u4f55\u6ce8\u518c","href":"http:\/\/www.qiqiuyu.com\/course\/347\/learn#lesson\/673","target":"_blank"},{"value":"\u5982\u4f55\u5b66\u4e60","href":"http:\/\/www.qiqiuyu.com\/course\/347\/learn#lesson\/705","target":"_blank"},{"value":"\u5982\u4f55\u4e92\u52a8","href":"http:\/\/www.qiqiuyu.com\/course\/347\/learn#lesson\/811","target":"_blank"}]},"secondColumnText":{"title":"\u7b2c\uff12\u5217\u94fe\u63a5\u6807\u9898","desc":"","count":1,"type":"text","default":[{"value":"\u6211\u662f\u8001\u5e08"}]},"secondColumnLinks":{"title":"\u7b2c\uff12\u5217\u94fe\u63a5","desc":"","count":5,"type":"link","default":[{"value":"\u53d1\u5e03\u8bfe\u7a0b","href":"http:\/\/www.qiqiuyu.com\/course\/22","target":"_blank"},{"value":"\u4f7f\u7528\u9898\u5e93","href":"http:\/\/www.qiqiuyu.com\/course\/147","target":"_blank"},{"value":"\u6559\u5b66\u8d44\u6599\u5e93","href":"http:\/\/www.qiqiuyu.com\/course\/372","target":"_blank"}]},"thirdColumnText":{"title":"\u7b2c\uff13\u5217\u94fe\u63a5\u6807\u9898","desc":"","count":1,"type":"text","default":[{"value":"\u6211\u662f\u7ba1\u7406\u5458"}]},"thirdColumnLinks":{"title":"\u7b2c\uff13\u5217\u94fe\u63a5","desc":"","count":5,"type":"link","default":[{"value":"\u7cfb\u7edf\u8bbe\u7f6e","href":"http:\/\/www.qiqiuyu.com\/course\/340","target":"_blank"},{"value":"\u8bfe\u7a0b\u8bbe\u7f6e","href":"http:\/\/www.qiqiuyu.com\/course\/341","target":"_blank"},{"value":"\u7528\u6237\u7ba1\u7406","href":"http:\/\/www.qiqiuyu.com\/course\/343","target":"_blank"}]},"fourthColumnText":{"title":"\u7b2c\uff14\u5217\u94fe\u63a5\u6807\u9898","desc":"","count":1,"type":"text","default":[{"value":"\u5546\u4e1a\u5e94\u7528"}]},"fourthColumnLinks":{"title":"\u7b2c\uff14\u5217\u94fe\u63a5\u6807\u9898","desc":"","count":5,"type":"link","default":[{"value":"\u4f1a\u5458\u4e13\u533a","href":"http:\/\/www.qiqiuyu.com\/course\/232\/learn#lesson\/358","target":"_blank"},{"value":"\u9898\u5e93\u589e\u5f3a\u7248","href":"http:\/\/www.qiqiuyu.com\/course\/232\/learn#lesson\/467","target":"_blank"},{"value":"\u7528\u6237\u5bfc\u5165\u5bfc\u51fa","href":"http:\/\/www.qiqiuyu.com\/course\/380","target":"_blank"}]},"fifthColumnText":{"title":"\u7b2c\uff15\u5217\u94fe\u63a5\u6807\u9898","desc":"","count":1,"type":"text","default":[{"value":"\u5173\u4e8e\u6211\u4eec"}]},"fifthColumnLinks":{"title":"\u7b2c\uff15\u5217\u94fe\u63a5\u6807\u9898","desc":"","count":5,"type":"link","default":[{"value":"ES\u5b98\u7f51","href":"http:\/\/www.edusoho.com\/","target":"_blank"},{"value":"\u5b98\u65b9\u5fae\u535a","href":"http:\/\/weibo.com\/qiqiuyu\/profile?rightmod=1&wvr=6&mod=personinfo","target":"_blank"},{"value":"\u52a0\u5165\u6211\u4eec","href":"http:\/\/www.edusoho.com\/abouts\/joinus","target":"_blank"}]},"bottomLogo":{"title":"\u5e95\u90e8Logo","desc":"\u5efa\u8bae\u56fe\u7247\u5927\u5c0f\u4e3a233*64","count":1,"type":"imglink","default":[{"src":"\/assets\/v2\/img\/bottom_logo.png","alt":"\u5efa\u8bae\u56fe\u7247\u5927\u5c0f\u4e3a233*64","href":"http:\/\/www.edusoho.com","target":"_blank"}]},"weibo":{"title":"\u5e95\u90e8\u5fae\u535a\u94fe\u63a5","desc":"\u586b\u5165\u7f51\u6821\u7684\u5fae\u535a\u9996\u9875\u5730\u5740","count":1,"type":"link","default":[{"value":"\u5fae\u535a\u9996\u9875","href":"http:\/\/weibo.com\/edusoho","target":"_blank"}]},"weixin":{"title":"\u5e95\u90e8\u5fae\u4fe1\u516c\u4f17\u53f7","desc":"\u4e0a\u4f20\u7f51\u6821\u7684\u5fae\u4fe1\u516c\u4f17\u53f7\u7684\u4e8c\u7ef4\u7801","count":1,"type":"img","default":[{"src":"\/assets\/img\/default\/weixin.png","alt":"\u5fae\u4fe1\u516c\u4f17\u53f7"}]},"apple":{"title":"\u5e95\u90e8iOS\u7248APP\u4e0b\u8f7d\u4e8c\u7ef4\u7801","desc":"\u4e0a\u4f20\u7f51\u6821\u7684iOS\u7248APP\u4e0b\u8f7d\u4e8c\u7ef4\u7801","count":1,"type":"img","default":[{"src":"\/assets\/img\/default\/apple.png","alt":"\u7f51\u6821\u7684iOS\u7248APP"}]},"android":{"title":"\u5e95\u90e8Android\u7248APP\u4e0b\u8f7d\u4e8c\u7ef4\u7801","desc":"\u4e0a\u4f20\u7f51\u6821\u7684Android\u7248APP\u4e0b\u8f7d\u4e8c\u7ef4\u7801","count":1,"type":"img","default":[{"src":"\/assets\/img\/default\/android.png","alt":"\u7f51\u6821\u7684Android\u7248APP"}]}}}';
        $connection->exec('update block set title="简墨主题: 首页底部.链接区域",meta="{$jianmoMeta3}" where code="jianmo:bottom_info"');
        //$jianmoMeta2 = '{"title":"\\u7b80\\u58a8\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u4e2d\\u90e8\\u002e\\u6a2a\\u5e45","category":"jianmo","templateName":"@theme\/jianmo\/block\/middle_banner.template.html.twig","items":{"icon1":{"title":"\u4e2d\u90e8\u56fe\u6807\uff11","desc":"\u5efa\u8bae\u56fe\u7247\u5927\u5c0f\u4e3a130*130","count":1,"type":"img","default":[{"src":"\/assets\/v2\/img\/icon_introduction_1.png","alt":"\\u4e2d\\u90e8\\u6a2a\\u5e45"}]},"icon1title":{"title":"\u56fe\u6807\uff11\u6807\u9898","desc":"","count":1,"type":"text","default":[{"value":"\u7f51\u6821\u529f\u80fd\u5f3a\u5927"}]},"icon1introduction":{"title":"\u56fe\u6807\uff11\u4ecb\u7ecd","desc":"","count":1,"type":"textarea","default":[{"value":"\u4e00\u4e07\u591a\u5bb6\u7f51\u6821\u5171\u540c\u9009\u62e9\uff0c\u503c\u5f97\u4fe1\u8d56"}]},"icon2":{"title":"\u4e2d\u90e8\u56fe\u6807\uff12","desc":"\u5efa\u8bae\u56fe\u7247\u5927\u5c0f\u4e3a130*130","count":1,"type":"img","default":[{"src":"\/assets\/v2\/img\/icon_introduction_2.png","alt":"\u4e2d\u90e8\u6a2a\u5e45"}]},"icon2title":{"title":"\u56fe\u6807\uff12\u6807\u9898","desc":"","count":1,"type":"text","default":[{"value":"\u54cd\u5e94\u5f0f\u9875\u9762\u6280\u672f"}]},"icon2introduction":{"title":"\u56fe\u6807\uff12\u4ecb\u7ecd","desc":"","count":1,"type":"textarea","default":[{"value":"\u91c7\u7528\u54cd\u5e94\u5f0f\u6280\u672f\uff0c\u5b8c\u7f8e\u9002\u914d\u4efb\u610f\u7ec8\u7aef"}]},"icon3":{"title":"\u4e2d\u90e8\u56fe\u6807\uff13","desc":"\u5efa\u8bae\u56fe\u7247\u5927\u5c0f\u4e3a130*130","count":1,"type":"img","default":[{"src":"\/assets\/v2\/img\/icon_introduction_3.png","alt":"\u4e2d\u90e8\u6a2a\u5e45"}]},"icon3title":{"title":"\u56fe\u6807\uff13\u6807\u9898","desc":"","count":1,"type":"text","default":[{"value":"\u6559\u80b2\u4e91\u670d\u52a1\u652f\u6301"}]},"icon3introduction":{"title":"\u56fe\u6807\uff13\u4ecb\u7ecd","desc":"","count":1,"type":"textarea","default":[{"value":"\u5f3a\u529b\u6559\u80b2\u4e91\u652f\u6301\uff0c\u514d\u9664\u4f60\u7684\u540e\u987e\u4e4b\u5fe7"}]}}}';
        $connection->exec('update block set title="简墨主题：首页中部.横幅",meta="{$jianmoMeta2}" where code="jianmo:middle_banner"');

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
