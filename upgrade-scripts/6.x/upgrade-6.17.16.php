<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    const VERSION = '6.17.16';

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);
            $this->getConnection()->commit();

            $this->updateCrontabSetting();

            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();

        try {
            $this->updateUploadFileInitsAutoIncrement();
        } catch (\Exception $e) {
            $this->logger('ERROR', $e->getMessage());
        }
    }

    protected function logger($level, $message)
    {
        $data = date("Y-m-d H:i:s").sprintf('[%s] %s %s', $level, self::VERSION, $message.PHP_EOL);
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    protected function getLoggerFile()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/logs/upgrade.log";
    }

    protected function updateUploadFileInitsAutoIncrement()
    {
        if ($this->isTableExist('upload_file_inits')) {
            $this->logger('info', '开始修改upload_file_inits的自增值');
            $sql             = "select max(id) as maxId from upload_files;";
            $uploadFileMaxId = $this->getConnection()->fetchAssoc($sql);

            $sql                 = "SELECT auto_increment FROM information_schema.`TABLES` WHERE table_schema='".$this->getSchema()."' AND table_name = 'upload_file_inits';";
            $uploadFileInitMaxId = $this->getConnection()->fetchAssoc($sql);

            if (empty($uploadFileMaxId['maxId'])) {
                $this->logger('info', 'upload_files没有记录');
            }

            if ($uploadFileMaxId['maxId'] >= $uploadFileInitMaxId['auto_increment']) {
                $start = $uploadFileMaxId['maxId'] + 1;
                $this->getConnection()->exec("alter table upload_file_inits AUTO_INCREMENT = {$start};");
                $this->logger('info', "成功修改upload_file_inits的自增值, {$start}");
            }

            $sql                = "select *  from upload_file_inits where id = {$uploadFileMaxId['maxId']} ;";
            $uploadFileInitsMax = $this->getConnection()->fetchAssoc($sql);
            if (empty($uploadFileInitsMax)) {
                $this->logger('info', "upload_file_inits需要插入一条数据,防止自增值回落");
                $this->getConnection()->exec("
                   insert into `upload_file_inits`  select `id`, `globalId`, `status`, `hashId`, `targetId`,`targetType`, `filename`,`ext`,`fileSize`,`etag`,`length`,  `convertHash`,  `convertStatus`, `metas`,  `metas2`,`type`, `storage`, `convertParams`,`updatedUserId` , `updatedTime` , `createdUserId`,  `createdTime`
                      from `upload_files` where id ={$uploadFileMaxId['maxId']};
                ");

                $uploadFileInitsMax = $this->getConnection()->fetchAssoc($sql);
                $this->logger('info', "upload_file_inits中insert了最大ID ＃{$uploadFileInitsMax['id']}");
            } else {
                $this->logger('info', "upload_file_inits中已存在最大ID, ＃{$uploadFileInitsMax['id']}");
            }
        }
    }

    private function getSchema()
    {
        return ServiceKernel::instance()->getParameter('database_name');
    }

    private function updateCrontabSetting()
    {
        $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/data/crontab_config.yml");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
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
