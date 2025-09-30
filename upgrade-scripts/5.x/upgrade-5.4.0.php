<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
        $this->getConnection()->beginTransaction();
        try{
            $this->updateScheme();

            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {

            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir))
            $filesystem->remove($dir);

        } catch(\Exception $e) {

        }

        $developerSetting = ServiceKernel::instance()->createService('System.SettingService')->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);


     }

    private function updateScheme()
     {
        $connection = $this->getConnection();

        if(!$this->isFieldExist('course', 'watchLimit')) {
            $connection->exec("ALTER TABLE  `course` ADD  `watchLimit` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '课时观看次数限制' AFTER `daysOfNotifyBeforeDeadline`;");
        }

        if(!$this->isFieldExist('course_lesson_learn', 'watchNum')) {
            $connection->exec("ALTER TABLE  `course_lesson_learn` ADD  `watchNum` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '课时已观看次数' AFTER  `watchTime`");
        }

        if (!$this->isTableExist('shortcut')) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS `shortcut` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL,
                  `title` varchar(255) NOT NULL DEFAULT '',
                  `url` varchar(255) NOT NULL DEFAULT '',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ");
        }

        if (!$this->isTableExist('announcement')) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS `announcement` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,`url` varchar(255) NOT NULL,
                `startTime` int(10) unsigned NOT NULL DEFAULT '0',
                `endTime` int(10) unsigned NOT NULL DEFAULT '0',
                `userId` int(10) unsigned NOT NULL DEFAULT '0',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ");
        }

    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
 }


 abstract class AbstractUpdater
 {
    protected $kernel;
    public function __construct ($kernel)
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