<?php

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Dao\CourseDao;
use Biz\Task\Service\TaskService;
use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\EdusohoLiveClient;

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
            $result = $this->updateScheme((int)$index);
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
            'addTableProductMallGoodsRelation',
            'userAddIsStudent',
            'addTableSyncList',
            'addTableMarketingMallAdminProfile',
            'refreshRoles',
        );
        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }
        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');

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

    public function addTableProductMallGoodsRelation()
    {
        $this->getConnection()->exec("CREATE TABLE IF NOT EXISTS `product_mall_goods_relation` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `productType` VARCHAR(32) DEFAULT 'course',
          `productId` int(11) DEFAULT 0 COMMENT '对应产品id',
          `goodsCode` VARCHAR(32) DEFAULT 0 COMMENT '营销商城商品编码',
          `createdTime` int(11) DEFAULT NULL,
          `updatedTime` int(11) DEFAULT NULL,
          UNIQUE KEY `productType_productId` (`productType`,`productId`),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '网校产品和营销商城关系表';");

        return 1;
    }

    public function userAddIsStudent()
    {
        if (!$this->isFieldExist('user', 'isStudent')) {
            $this->getConnection()->exec("ALTER TABLE `user` ADD COLUMN `isStudent` tinyint(1)  NOT NULL DEFAULT 1 COMMENT '是否为学员'");
        }

        return 1;
    }

    public function addTableSyncList()
    {
        $this->getConnection()->exec("CREATE TABLE IF NOT EXISTS `sync_list` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
          `type` varchar(32) NOT NULL COMMENT '类型',
          `status` varchar(16) NOT NULL DEFAULT 'new' COMMENT '消息状态',
          `data` varchar(300) NOT NULL COMMENT '需要更新数据信息',
          `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
          `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        return 1;
    }

    public function addTableMarketingMallAdminProfile()
    {
        $this->getConnection()->exec("CREATE TABLE IF NOT EXISTS `marketing_mall_admin_profile` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `userId` int(11) unsigned NOT NULL COMMENT '用户id',
          `field` VARCHAR(32) NOT NULL COMMENT '配置项',
          `val` VARCHAR(64) NOT NULL DEFAULT '0',
          `createdTime` int(11) unsigned NOT NULL DEFAULT 0,
          `updatedTime` int(11) unsigned NOT NULL DEFAULT 0,
          UNIQUE KEY `user_field` (`userId`,`field`),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '营销商城管理员设置表';");

        return 1;
    }

    public function refreshRoles()
    {
        $this->getRoleService()->refreshRoles();

        return 1;
    }

    protected function installPluginAssets($plugins)
    {
        $rootDir = realpath($this->biz['kernel.root_dir'].'/../');
        foreach ($plugins as $plugin) {
            $pluginApp = $this->getAppService()->getAppByCode($plugin);
            if (empty($pluginApp)) {
                continue;
            }
            $originDir = "{$rootDir}/plugins/{$plugin}Plugin/Resources/public";
            $targetDir = "{$rootDir}/web/bundles/".strtolower($plugin).'plugin';
            $filesystem = new Filesystem();
            if ($filesystem->exists($targetDir)) {
                $filesystem->remove($targetDir);
            }
            if ($filesystem->exists($originDir)) {
                $filesystem->mirror($originDir, $targetDir, null, ['override' => true, 'delete' => true]);
            }
            $originDir = "{$rootDir}/plugins/{$plugin}Plugin/Resources/static-dist/".strtolower($plugin).'plugin/';
            if (!is_dir($originDir)) {
                return false;
            }
            $targetDir = "{$rootDir}/web/static-dist/".strtolower($plugin).'plugin/';
            $filesystem = new Filesystem();
            $filesystem->mirror($originDir, $targetDir, null, ['override' => true, 'delete' => true]);
        }
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

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    /**
     * @return TaskService
     */
    public function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getJobLogDao()
    {
        return $this->createDao('Scheduler:JobLogDao');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
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

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform:CloudAppLogDao');
    }
}
