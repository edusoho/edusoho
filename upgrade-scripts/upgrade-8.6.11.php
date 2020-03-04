<?php

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

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
            'addPasswordConfigIfRedisEnable'
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

    public function addPasswordConfigIfRedisEnable()
    {
        $configFile = $this->biz['kernel.root_dir'].'/../app/config/parameters.yml';
        $filesystem = new Filesystem();
        $parameters = Yaml::parse(file_get_contents($configFile));
        if (!empty($parameters['parameters']['redis_host']) && empty($parameters['parameters']['redis_password'])) {
            $filesystem->copy($configFile, dirname($configFile).'/parameters.yml.bak');
            $parameters = $this->fillConfig($parameters);
            file_put_contents($configFile, Yaml::dump($parameters));
        }

        return 1;
    }

    private function fillConfig($config)
    {
        $dbConfig = $this->biz['db.options'];
        $parameters = empty($config['parameters']) ? array() : $config['parameters'];
        $parameters['database_driver'] = empty($parameters['database_driver']) ? $dbConfig['driver'] : $parameters['database_driver'];
        $parameters['database_host'] = empty($parameters['database_host']) ? $dbConfig['host'] : $parameters['database_host'];
        $parameters['database_port'] = empty($parameters['database_port']) ? $dbConfig['port'] : $parameters['database_port'];
        $parameters['database_name'] = empty($parameters['database_name']) ? $dbConfig['dbname'] : $parameters['database_name'];
        $parameters['database_user'] = empty($parameters['database_user']) ? $dbConfig['user'] : $parameters['database_user'];
        $parameters['database_password'] = empty($parameters['database_password']) ? $dbConfig['password'] : $parameters['database_password'];
        $parameters['locale'] = empty($parameters['locale']) ? 'zh_CN' : $parameters['locale'];
        $parameters['secret'] = empty($parameters['secret']) ? 'ThisTokenIsNotSoSecretChangeIt' : $parameters['secret'];

        $parameters['redis_host'] = empty($parameters['redis_host']) ? '127.0.0.1:6379' : $parameters['redis_host'];
        $parameters['redis_timeout'] = empty($parameters['redis_timeout']) ? 1 : $parameters['redis_timeout'];
        $parameters['redis_reserved'] = empty($parameters['redis_reserved']) ? null : $parameters['redis_reserved'];
        $parameters['redis_retry_interval'] = empty($parameters['redis_retry_interval']) ? 100 : $parameters['redis_retry_interval'];
        $parameters['redis_password'] = empty($parameters['redis_password']) ? '' : $parameters['redis_password'];

        $config['parameters'] = $parameters;

        return $config;
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