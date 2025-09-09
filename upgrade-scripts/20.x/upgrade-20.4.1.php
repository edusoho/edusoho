<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;

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
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
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
            'infoCollectTable',
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

    public function infoCollectTable()
    {
        if (!$this->isTableExist('information_collect_event')) {
            $this->getConnection()->exec("
                CREATE TABLE `information_collect_event` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `title` varchar(256) NOT NULL COMMENT '信息采集的标题',
                  `action` varchar(32) NOT NULL COMMENT '信息采集的位置行为buy_after=购买后，buy_before=购买前',
                  `formTitle` varchar(64) NOT NULL COMMENT '信息采集表单的标题',
                  `status` varchar(32) NOT NULL DEFAULT 'open' COMMENT '信息采集开启状态,open:开启;close:关闭',
                  `allowSkip` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许跳过',
                  `creator` int(11) unsigned NOT NULL COMMENT '创建者',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `action` (`action`),
                  KEY `title` (`title`(255))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信息采集事件表';
            ");
        }

        if (!$this->isTableExist('information_collect_item')) {
            $this->getConnection()->exec("
                CREATE TABLE `information_collect_item` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `eventId` int(11) unsigned NOT NULL COMMENT '采集事件ID',
                  `code` varchar(32) NOT NULL COMMENT '表单的code，作为表单的name',
                  `labelName` varchar(32) NOT NULL COMMENT '表单的标签名',
                  `seq` int(10) unsigned NOT NULL COMMENT '表单位置顺序',
                  `required` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否必填',
                  `createdTime` int(10) unsigned DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `eventId` (`eventId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='采集事件表单项';
            ");
        }

        if (!$this->isTableExist('information_collect_location')) {
            $this->getConnection()->exec("
                CREATE TABLE `information_collect_location` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `eventId` int(11) unsigned NOT NULL COMMENT '采集事件ID',
                  `action` varchar(32) NOT NULL DEFAULT '' COMMENT '信息采集的位置行为',
                  `targetType` varchar(32) NOT NULL COMMENT '目标类型，比如course,classroom,none',
                  `targetId` int(11) DEFAULT NULL COMMENT '目标ID 0为当前类型全部',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `targetType` (`targetType`),
                  KEY `targetId` (`targetId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信息采集位置';
            ");
        }

        if (!$this->isTableExist('information_collect_result')) {
            $this->getConnection()->exec("
                CREATE TABLE `information_collect_result` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `formTitle` varchar(64) NOT NULL COMMENT '表单标题',
                  `userId` int(11) unsigned NOT NULL COMMENT '提交人',
                  `eventId` int(11) unsigned NOT NULL COMMENT '采集事件ID',
                  `createdTime` int(10) unsigned NOT NULL,
                  `updatedTime` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `userId` (`userId`),
                  KEY `eventId` (`eventId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据采集结果';
            ");
        }

        if (!$this->isTableExist('information_collect_result_item')) {
            $this->getConnection()->exec("
                CREATE TABLE `information_collect_result_item` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `eventId` int(11) unsigned NOT NULL COMMENT '采集事件ID',
                  `resultId` int(11) unsigned NOT NULL COMMENT '采集结果ID',
                  `code` varchar(32) NOT NULL COMMENT '表单的code，作为表单的name',
                  `labelName` varchar(32) NOT NULL COMMENT '表单的标签名',
                  `value` varchar(4096) NOT NULL DEFAULT '' COMMENT '表单值',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `eventId` (`eventId`),
                  KEY `resultId` (`resultId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信息采集表单值';
            ");
        }
        $this->logger('info', '创建用户信息采集表！');
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

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
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

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
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
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
    }

    /**
     * @return \Biz\DiscoveryColumn\Service\DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return \Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\System\Service\H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
