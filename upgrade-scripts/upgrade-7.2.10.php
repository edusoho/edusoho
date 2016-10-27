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
         } catch (\Exception $e) {
             $this->getConnection()->rollback();
             throw $e;
         }

        try {
             $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
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

        if (!$this->isTableExist('user_active_log')) {
            $connection->exec("
            CREATE TABLE `user_active_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `userId` int(11) NOT NULL COMMENT '用户Id',
              `activeTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '激活时间',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `createdTime` (`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活跃用户记录表';
            INSERT INTO user_active_log (userid, activeTime,createdTime) SELECT `sess_user_id`, FROM_UNIXTIME(`sess_time`, '%Y%m%d'),`sess_time` FROM `sessions`;
            ");
        }

        if (!$this->isFieldExist('course', 'buyExpireTime')) {
            $connection->exec("ALTER TABLE `course` CHANGE `buyExpireTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买开放有效期'");
        }
        if (!$this->isFieldExist('course', 'buyExpiryTime')) {
            $connection->exec("ALTER TABLE `course` CHANGE `buyExpiryTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买开放有效期'");
        }

        if (!$this->isIndexExist('status', 'courseId_createdTime') && $this->isFieldOwnIndex('status', 'courseId') && $this->isFieldExist('status', 'createdTime')) {
            $connection->exec("ALTER TABLE status ADD INDEX courseId_createdTime (courseId, createdTime);");
        }
    }

    protected function isFieldOwnIndex($table, $fieldName) {
        $sql = "show index from `{$table}` where column_name like '{$fieldName}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
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
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
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
