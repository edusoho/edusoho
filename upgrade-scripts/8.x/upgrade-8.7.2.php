<?php

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
            'createS2B2CTables',
            'courseV8AddPlatform',
            'courseSetV8AddPlatform',
            'tableAddSyncIdCourseTask',
            'tableAddSyncIdFileUsed',
            'tableAddSyncIdActivity',
            'tableAddSyncIdActivityDownload',
            'tableAddSyncIdActivityVideo',
            'tableAddSyncIdActivityHomework',
            'tableAddSyncIdActivityExercise',
            'tableAddSyncIdActivityAudio',
            'tableAddSyncIdActivityDoc',
            'tableAddSyncIdActivityPpt',
            'tableAddSyncIdActivityFlash',
            'tableAddSyncIdActivityText',
            'tableAddSyncIdActivityTestpaper',
            'tableAddSyncIdActivityLive',
            'tableAddSyncIdActivityCourseMaterialV8',
            'tableAddSyncIdActivityUploadFiles',
            'tableAddSyncIdActivityCourseChapter',
            'uploadFileChangeStorageColumn',
            'addPlumberQueueTable',
            'addSyncEventsTable',
            'addResourceSyncTable',
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

    public function createS2B2CTables()
    {
        if (!$this->isTableExist('s2b2c_product')) {
            $this->getConnection()->exec("
                CREATE TABLE `s2b2c_product` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `supplierId` int(10) unsigned NOT NULL COMMENT '平台对应的ID：supplierId S的ID',
                  `productType` varchar(64) NOT NULL DEFAULT '' COMMENT '产品类型',
                  `remoteProductId` int(10) unsigned NOT NULL COMMENT '远程产品ID',
                  `remoteResourceId` int(10) unsigned NOT NULL COMMENT '远程产品对应资源ID',
                  `localResourceId` int(10) unsigned NOT NULL COMMENT '本地产品对应资源ID',
                  `cooperationPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '合作价格',
                  `suggestionPrice` float(10,2) DEFAULT '0.00' COMMENT '建议零售价',
                  `localVersion` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '本地版本:默认1',
                  `remoteVersion` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '远程版本:默认1',
                  `syncStatus` varchar(32) NOT NULL DEFAULT 'waiting' COMMENT '产品资源同步状态 waiting,finished',
                  `changelog` mediumtext COMMENT '更新日志',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `remoteProductId` (`remoteProductId`),
                  KEY `remoteResourceId` (`remoteResourceId`),
                  KEY `localResourceId` (`localResourceId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        return 1;
    }

    public function courseV8AddPlatform()
    {
        if (!$this->isFieldExist('course_v8', 'platform')) {
            $this->getConnection()->exec("
                ALTER TABLE `course_v8` 
                ADD `platform` varchar(32) NOT NULL DEFAULT 'self' COMMENT '课程来源平台：self 自己平台创建，supplier S端提供';
            ");
        }
        return 1;
    }

    public function courseSetV8AddPlatform()
    {
        if (!$this->isFieldExist('course_set_v8', 'platform')) {
            $this->getConnection()->exec("
               ALTER TABLE `course_set_v8` 
                ADD `platform` varchar(32) NOT NULL DEFAULT 'self' COMMENT '课程来源平台：self 自己平台创建，supplier S端提供';
            ");
        }
        return 1;
    }

    public function tableAddSyncIdCourseTask()
    {
        if (!$this->isFieldExist('course_task', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `course_task` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdFileUsed()
    {
        if (!$this->isFieldExist('file_used', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `file_used` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivity()
    {
        if (!$this->isFieldExist('activity', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityDownload()
    {
        if (!$this->isFieldExist('activity_download', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_download` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityVideo()
    {
        if (!$this->isFieldExist('activity_video', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_video` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityHomework()
    {
        if (!$this->isFieldExist('activity_homework', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_homework` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityExercise()
    {
        if (!$this->isFieldExist('activity_exercise', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_exercise` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityAudio()
    {
        if (!$this->isFieldExist('activity_audio', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_audio` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityDoc()
    {
        if (!$this->isFieldExist('activity_doc', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_doc` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityPpt()
    {
        if (!$this->isFieldExist('activity_ppt', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_ppt` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityFlash()
    {
        if (!$this->isFieldExist('activity_flash', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_flash` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityText()
    {
        if (!$this->isFieldExist('activity_text', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_text` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityTestpaper()
    {
        if (!$this->isFieldExist('activity_testpaper', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_testpaper` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityLive()
    {
        if (!$this->isFieldExist('activity_live', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_live` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityCourseMaterialV8()
    {
        if (!$this->isFieldExist('course_material_v8', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `course_material_v8` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityUploadFiles()
    {
        if (!$this->isFieldExist('upload_files', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `upload_files` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function tableAddSyncIdActivityCourseChapter()
    {
        if (!$this->isFieldExist('course_chapter', 'syncId')) {
            $this->getConnection()->exec("ALTER TABLE `course_chapter` ADD COLUMN `syncId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容市场引用的源Id';");
        }
        return 1;
    }

    public function uploadFileChangeStorageColumn()
    {
        $this->getConnection()->exec("
            ALTER TABLE `upload_files` MODIFY COLUMN `storage` ENUM('local','cloud','supplier') NOT NULL COMMENT '文件存储方式';
            ALTER TABLE `upload_file_inits` MODIFY COLUMN `storage` ENUM('local','cloud','supplier') NOT NULL COMMENT '文件存储方式';
        ");

        if (!$this->isFieldExist('upload_files', 'originPlatform')) {
            $this->getConnection()->exec("
                ALTER TABLE `upload_files` 
                ADD `originPlatform` varchar(50) NOT NULL DEFAULT 'self' COMMENT '资源来源平台：self 自己平台创建，supplier S端提供',
                ADD `originPlatformId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源平台对应的ID：supplierId S的ID',
                ADD `s2b2cGlobalId` varchar(32) NOT NULL DEFAULT '' COMMENT '真实的globalId，防止资源引用无法知道真实分发信息',
                ADD `s2b2cHashId` varchar(128) NOT NULL DEFAULT '' COMMENT '真实的hashId，防止资源引用无法知道真实分发信息';
            ");
        }

        return 1;
    }

    public function addPlumberQueueTable()
    {
        if (!$this->isTableExist('plumber_queue')) {
            $this->getConnection()->exec("
             CREATE TABLE  IF NOT EXISTS  `plumber_queue`(
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `worker` VARCHAR(64) NOT NULL COMMENT 'workerTopic',
                `jobId` VARCHAR(64) NOT NULL COMMENT 'jobId',
                `body` TEXT NOT NULL COMMENT 'Job消息主体', 
                `status` VARCHAR(32) NOT NULL DEFAULT 'acquired' COMMENT 'Job执行状态', 
                `priority` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '优先级', 
                `trace` LONGTEXT DEFAULT NULL COMMENT '异常信息', 
                `createdTime` INT(10) UNSIGNED NOT NULL DEFAULT 0, 
                `cupdatedTime` INT(10) UNSIGNED NOT NULL DEFAULT 0, 
                PRIMARY KEY (`id`)
             ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
        ");
        }
        return 1;
    }

    public function addSyncEventsTable()
    {
        if (!$this->isTableExist('s2b2c_sync_event')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `s2b2c_sync_event` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `event` varchar(255) NOT NULL COMMENT '事件名称',
                  `data` text COMMENT '内容',
                  `isConfirm` tinyint(3) NOT NULL DEFAULT 0 COMMENT '是否确认',
                  `productId` int(10) NOT NULL DEFAULT 0 COMMENT '资源id',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"
            );
        }
        return 1;
    }

    public function addResourceSyncTable()
    {
        if (!$this->isTableExist('s2b2c_resource_sync')) {
            $this->getConnection()->exec("
                    CREATE TABLE `s2b2c_resource_sync` (
                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                      `supplierId` int(10) unsigned NOT NULL COMMENT '供应商ID',
                      `resourceType` varchar(64) NOT NULL DEFAULT '' COMMENT '资源类型',
                      `localResourceId` int(10) unsigned NOT NULL COMMENT '本地资源ID',
                      `remoteResourceId` int(10) unsigned NOT NULL COMMENT '远程资源ID',
                      `localVersion` varchar(32) DEFAULT NULL COMMENT '本地版本',
                      `remoteVersion` varchar(32) DEFAULT NULL COMMENT '远程版本',
                      `extendedData` text COMMENT '其他关联数据',
                      `syncTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '同步时间',
                      `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                      `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      KEY `supplierId_remoteResourceId_type` (`supplierId`,`remoteResourceId`,`resourceType`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                ");
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
