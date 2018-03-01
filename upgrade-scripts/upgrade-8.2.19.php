<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;
    private $userUpdateHelper = null;
    private $batchUUIDs = array();

    public function __construct($biz)
    {
        parent::__construct($biz);

        $this->userUpdateHelper = new BatchUpdateHelper($this->getUserDao());
        $this->generateUUID();
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
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger( 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'addUserUUID',
            'updateUserUUID',
            'addUserIndex'
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key+1] = $funcName;
        }


        if ($index == 0) {
            $this->logger( 'info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            $step++;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0
            );
        }
    }

    protected function addUserUUID()
    {
        if (!$this->isFieldExist('user', 'uuid')) {
            $this->getConnection()->exec("ALTER TABLE `user` ADD COLUMN `uuid` varchar(255)  NOT NULL DEFAULT '' COMMENT '用户uuid';");
        }
        $this->logger('info', '添加uuid字段结束');
        
        return 1;
    }

    protected function updateUserUUID($page)
    {
        $limit = 1000;
        $start = ($page - 1) * $limit;
        $sql = "SELECT COUNT(id) FROM `user` WHERE uuid = '';"; 
        $count = $this->getConnection()->fetchColumn($sql);

        if (empty($count)) {
            return 1;
        }

        $sql = "SELECT id FROM `user` WHERE uuid = '' LIMIT {$start}, {$limit};";
        $users = $this->getConnection()->fetchAll($sql);

        foreach ($users as $user) {
            if (empty($this->batchUUIDs)) {
                $this->generateUUID();
            }
            $uuid = array_shift($this->batchUUIDs);
            $fields = array('uuid' => $uuid);
            $this->userUpdateHelper->add('id', $user['id'], $fields);
        }

        $this->userUpdateHelper->flush();

        $this->logger('info', '更新uuid字段，共有'.$count.'条，已处理'.$limit.'条，当前页码：'.$page);

        return $page + 1;
    }

    protected function addUserIndex()
    {
        if (!$this->isIndexExist('user', 'uuid', 'uuid')) {
            $this->getConnection()->exec('CREATE UNIQUE INDEX uuid ON user(`uuid`);');
        }
        $this->logger('info', '添加uuid唯一索引');
        return 1;
    }

    private function generateUUID()
    {
        $count = 10000;

        $exists = array();
        if ($this->isFieldExist('user', 'uuid')) {
            $sql = "SELECT uuid FROM user WHERE uuid != '';";
            $users = $this->getConnection()->fetchAll($sql);
            $exists = ArrayToolkit::column($users, 'uuid');
        }

        for ($i = 1; $i<=$count; $i++) {
            $uuid = $this->makeUUID();
            if (!in_array($uuid, $this->batchUUIDs) && !in_array($uuid, $exists)) {
                $this->batchUUIDs[] = $uuid;
            }
        }

        return $this->batchUUIDs;
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