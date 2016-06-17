<?php

use Topxia\Common\BlockToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    const VERSION = '6.17.14';

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

        if (!$this->isFieldExist('course_material', 'source')) {
            $connection->exec("ALTER TABLE `course_material` ADD `source` varchar(50) NOT NULL DEFAULT 'coursematerial' AFTER `fileSize`;");
        }
        
        try{
            $this->updateUploadFileInitsAutoIncrement();

            $connection->exec("ALTER TABLE `course_member` ADD INDEX `courseId_role_createdTime` (`courseId`, `role`, `createdTime`);");
            $this->logger('INFO', '建立course_member索引courseId_role_createdTime');

            $connection->exec("ALTER TABLE `message_conversation` ADD INDEX `toId_fromId` (`toId`, `fromId`);");
            $this->logger('INFO', '建立message_conversation索引toId_fromId');
            $connection->exec("ALTER TABLE `message_conversation` ADD INDEX `toId_latestMessageTime` (`toId`, `latestMessageTime`);");
            $this->logger('INFO', '建立message_conversation索引toId_latestMessageTime');

            $connection->exec("ALTER TABLE orders MODIFY payment VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT '订单支付方式';");
            $this->logger('INFO', '更改orders表字段payment结构为VARCHAR(32)');
            $connection->exec("UPDATE orders o SET payment = 'outside' WHERE (SELECT userId FROM order_log ol WHERE o.id = ol.orderId AND ol.type='created' order by ol.createdTime desc limit 1) IS NOT NULL AND userId != (SELECT userId FROM order_log ol WHERE o.id = ol.orderId AND ol.type='created' order by ol.createdTime desc limit 1)  AND o.status='paid';");
            $this->logger('INFO', '将之前管理员导入的订单order表记录payment字段值改为outside');

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
            $sql    = "select max(id) as maxId from upload_files;";
            $uploadFileMaxId = $this->getConnection()->fetchAssoc($sql);

            $sql    = "SELECT auto_increment FROM information_schema.`TABLES` WHERE table_schema='".$this->getSchema()."' AND table_name = 'upload_file_inits';";
            $uploadFileInitMaxId = $this->getConnection()->fetchAssoc($sql);

            if(empty($uploadFileMaxId['maxId'])) {
                $this->logger('info', 'upload_files没有记录');
                return;
            }

            if($uploadFileMaxId['maxId']<$uploadFileInitMaxId['auto_increment']){
                $this->logger('info', "upload_files表的最大id小于init表的自增值, uploadFileMaxId: {$uploadFileMaxId['maxId']}, uploadFileInitIncrement: {$uploadFileInitMaxId['auto_increment']}");
                return;
            }

            $start = $uploadFileMaxId['maxId'] + 10;
            $this->getConnection()->exec("alter table upload_file_inits AUTO_INCREMENT = {$start};");
            $this->logger('info', "成功修改upload_file_inits的自增值, {$start}");
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
