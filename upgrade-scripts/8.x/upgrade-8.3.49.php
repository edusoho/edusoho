<?php

use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            } else {
                $this->logger('info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger('error', $e->getTraceAsString());
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'createOauthTables',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if (1 == $page) {
            ++$step;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }
    }

    public function createOauthTables()
    {
        if (!$this->isTableExist('oauth_access_token')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `oauth_access_token` (
                  `token` varchar(40) NOT NULL COMMENT '授权TOKEN',
                  `client_id` varchar(50) DEFAULT NULL COMMENT '客户端ID',
                  `user_id` varchar(100) DEFAULT NULL COMMENT '用户ID',
                  `expires` datetime NOT NULL COMMENT '有效期',
                  `scope` varchar(50) DEFAULT NULL COMMENT '授权范围',
                  PRIMARY KEY (`token`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权令牌表';
            ");
        }

        if (!$this->isTableExist('oauth_authorization_code')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `oauth_authorization_code` (
                  `code` varchar(40) NOT NULL COMMENT '授权码',
                  `client_id` varchar(50) DEFAULT NULL COMMENT '客户端ID',
                  `expires` datetime NOT NULL COMMENT '有效期',
                  `user_id` varchar(100) DEFAULT NULL COMMENT '用户ID',
                  `redirect_uri` longtext NOT NULL COMMENT '客户端授权登陆回调地址',
                  `scope` varchar(255) DEFAULT NULL COMMENT '授权范围',
                  `id_token` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权码表';
            ");
        }

        if (!$this->isTableExist('oauth_client')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `oauth_client` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `client_id` varchar(50) NOT NULL DEFAULT '' COMMENT '客户端ID',
                  `client_secret` varchar(40) NOT NULL DEFAULT '' COMMENT '客户端secret',
                  `redirect_uri` text NOT NULL COMMENT '客户端授权登陆回调地址',
                  `grant_types` text COMMENT '授权类型',
                  `scopes` text COMMENT '授权范围',
                  `created_user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建用户ID',
                  `created_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权客户端表';
            ");
        }

        if (!$this->isTableExist('oauth_client_public_key')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `oauth_client_public_key` (
                  `client_id` varchar(50) NOT NULL,
                  `public_key` longtext NOT NULL,
                  PRIMARY KEY (`client_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('oauth_refresh_token')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `oauth_refresh_token` (
                  `token` varchar(40) NOT NULL COMMENT '授权TOKEN',
                  `client_id` varchar(50) DEFAULT NULL COMMENT '客户端ID',
                  `user_id` varchar(100) DEFAULT NULL COMMENT '用户ID',
                  `expires` datetime NOT NULL COMMENT '有效期',
                  `scope` varchar(255) DEFAULT NULL COMMENT '授权类型',
                  PRIMARY KEY (`token`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权刷新令牌表';
            ");
        }

        if (!$this->isTableExist('oauth_scope')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `oauth_scope` (
                  `scope` varchar(255) NOT NULL COMMENT '授权范围',
                  `description` varchar(255) NOT NULL COMMENT '授权范围描述',
                  PRIMARY KEY (`scope`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权范围表';
            ");
        }

        if (!$this->isTableExist('oauth_user')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `oauth_user` (
                  `username` varchar(255) NOT NULL COMMENT '用户名',
                  `password` varchar(255) NOT NULL COMMENT '密码',
                  `salt` varchar(255) NOT NULL,
                  `roles` longtext COMMENT '用户角色',
                  `scopes` longtext COMMENT '用户授权范围',
                  PRIMARY KEY (`username`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权用户';
            ");
        }

        return 1;
    }

    public function updateWechatNotificationSetting()
    {
        $wechatSetting = $this->getSettingService()->get('wechat');

        try {
            if (!empty($wechatSetting['templates']['liveOpen']['templateId'])) {
                $client = $this->biz['wechat.template_message_client'];
                if (empty($client)) {
                    $this->logger('info', '获取微信信息错误');
                    return 1;
                }

                if (!empty($wechatSetting['is_authorization'])) {
                    $sdk = $this->biz['qiQiuYunSdk.wechat'];
                    $data = $sdk->createNotificationTemplate(TemplateUtil::TEMPLATE_LIVE_OPEN_CODE);
                } else {
                    $data = $client->addTemplate(TemplateUtil::TEMPLATE_LIVE_OPEN_CODE);
                }

                if (empty($data)) {
                    $this->logger('info', '模板打开失败');
                    return 1;
                }

                $wechatSetting['templates']['liveOpen']['templateId'] = $data['template_id'];

                $this->getSettingService()->set('wechat', $wechatSetting);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }


        return 1;
    }

    public function registerUpdateTestpaperJob()
    {
        if ($this->isTableExist('biz_scheduler_job')) {
            $currentTime = time();
            $time = strtotime(date('Y-m-d' . ' 01:00:00', strtotime("+1 day")));
            $this->getConnection()->exec("INSERT INTO `biz_scheduler_job` (
                `name`,
                `expression`,
                `class`,
                `args`,
                `priority`,
                `pre_fire_time`,
                `next_fire_time`,
                `misfire_threshold`,
                `misfire_policy`,
                `enabled`,
                `creator_id`,
                `updated_time`,
                `created_time`
            ) VALUES (
              'UpdateTestpaperTotalScoresJob',
              '',
              'Biz\\\\Testpaper\\\\\Job\\\\UpdateTestpaperTotalScoresJob',
              '',
              '200',
              '0',
              '{$time}',
              '300',
              'executing',
              '1',
              '0',
              '{$currentTime}',
              '{$currentTime}'
        )");
            $this->logger('info', '试卷各题型总分计算定时任务');
        }
        return 1;
    }


    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;

        return array($step, $page);
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

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    private function makeUUID()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Dao\JobDao
     */
    protected function getJobDao()
    {
        return $this->createDao('Scheduler:JobDao');
    }

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }
}

abstract class AbstractUpdater
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getConnection()
    {
        return $this->biz['db'];
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    abstract public function update();

    protected function logger($level, $message)
    {
        $version = \AppBundle\System::VERSION;
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
    }
}
