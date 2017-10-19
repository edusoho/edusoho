<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->logger('begin to upgrade scripts', 'info');
            $this->updateScheme();
            $this->getConnection()->commit();
            $this->logger('upgrade end', 'info');
        } catch (\Exception $e) {
            $this->logger($e->getMessage(), 'error');
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger($e->getMessage(), 'error');
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        $this->changeMainAppType();
        $this->addIsDeleteForMessage();
        $this->updateOldUserApprovals();
    }


    private function addIsDeleteForMessage()
    {
        if (!$this->isFieldExist('message', 'isDelete')) {
            $this->getConnection()->exec("ALTER TABLE `message` ADD isDelete INT(1) NOT NULL DEFAULT '0' COMMENT '是否已删除';");
            $this->logger('ALTER TABLE `message` ADD isDelete INT(1) NOT NULL DEFAULT \'0\' COMMENT \'是否已删除\';');
        }
    }

    /**
     * 为了保证 通用版，国际版的 code 不与 插件混用，将其类型改为core
     */
    private function changeMainAppType()
    {
        $this->getConnection()->exec("UPDATE cloud_app SET type ='core' WHERE code = 'MAIN';");
        $this->logger('UPDATE cloud_app SET type =\'core\' WHERE code = \'MAIN\';');
    }

    //以前的数据认证通过之后没有改status的状态，所以需要统一修改
    protected function updateOldUserApprovals()
    {
        $this->getConnection()->exec("UPDATE user_approval AS ua, user AS u SET ua.status = 'approved' WHERE ua.userId = u.id AND u.approvalStatus = 'approved' AND ua.status = 'approving'");
        $this->logger('UPDATE user_approval AS ua, user AS u SET ua.status = \'approved\' WHERE ua.userId = u.id AND u.approvalStatus = \'approved\' AND ua.status = \'approving\'');
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

    protected function logger($message, $level = 'info')
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

    abstract public function update();
}
