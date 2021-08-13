<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    protected $fontDownloadUrl = 'https://edusoho-official.oss-cn-hangzhou.aliyuncs.com/edusoho-release/SourceHanSerifCNBold.otf';

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
            'addCertificateTables',
            'addUserColumn',
            'downloadFont',
            'addCheckCertificateJob',
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

    public function addCertificateTables()
    {
        if (!$this->isTableExist('certificate_template')) {
            $this->getConnection()->exec("
                CREATE TABLE `certificate_template` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL COMMENT '模板名称',
                    `targetType` varchar(64) DEFAULT '' COMMENT '发放类型',
                    `basemap` VARCHAR (255) DEFAULT '' COMMENT '底图',
                    `stamp` VARCHAR (255) DEFAULT '' COMMENT '印章',
                    `styleType`  VARCHAR (32) DEFAULT 'horizontal' COMMENT '样式类型,horizontal横版,vertical竖版',
                    `certificateName` VARCHAR (255) DEFAULT '' COMMENT '证书标题',
                    `recipientContent` VARCHAR (255) DEFAULT '' COMMENT '被授予人信息',
                    `certificateContent` text COMMENT '证书正文',
                    `qrCodeSet` tinyint(1) unsigned DEFAULT 1 COMMENT '二维码设置',
                    `createdUserId` INT(10) unsigned DEFAULT '0' COMMENT '创建者Id',
                    `dropped` tinyint(1) unsigned DEFAULT 0 COMMENT '是否废弃',
                    `createdTime` INT(10) unsigned DEFAULT '0' COMMENT '创建时间',
                    `updatedTime` INT(10) unsigned DEFAULT '0'  COMMENT '更新时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='证书模板表';
            ");
        }

        if (!$this->isTableExist('certificate')) {
            $this->getConnection()->exec("
                CREATE TABLE `certificate` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL COMMENT '证书名称',
                    `targetType` varchar(64) DEFAULT '' COMMENT '发放类型',
                    `targetId` int(10) DEFAULT '0' COMMENT '发放对象ID',
                    `description` text COMMENT '证书描述',
                    `templateId` int(10) DEFAULT '0' COMMENT '底图',
                    `code` VARCHAR (255) DEFAULT '' COMMENT '证书编码',
                    `status` varchar(64) DEFAULT 'draft' COMMENT '证书状态',
                    `expiryDay` int(10) DEFAULT '0' COMMENT '有效期天数，0表示长期有效',
                    `autoIssue` tinyint(1) unsigned DEFAULT '1' COMMENT '是否自动发放',
                    `createdUserId` INT(10) unsigned DEFAULT '0' COMMENT '创建者Id',
                    `createdTime` INT(10) unsigned DEFAULT '0' COMMENT '创建时间',
                    `updatedTime` INT(10) unsigned DEFAULT '0'  COMMENT '更新时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='证书表';
            ");
        }

        if (!$this->isTableExist('certificate_record')) {
            $this->getConnection()->exec("
                CREATE TABLE `certificate_record` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `userId` int(10) COMMENT '用户ID',
                    `certificateId` int(10) COMMENT '证书ID',
                    `certificateCode` VARCHAR (255) DEFAULT '' COMMENT '证书编码',
                    `targetType` varchar(64) DEFAULT '' COMMENT '发放类型',
                    `targetId` int(10) DEFAULT '0' COMMENT '发放对象ID',
                    `status` varchar(64) DEFAULT 'none' COMMENT '证书审核状态,none未审核,valid有效,expired过期,cancelled作废',
                    `rejectReason` VARCHAR (255) DEFAULT '' COMMENT '拒绝原因',
                    `auditUserId` INT(10) unsigned DEFAULT '0' COMMENT '审核用户ID',
                    `auditTime` INT(10) unsigned DEFAULT '0' COMMENT '审核时间',
                    `expiryTime` int(10) DEFAULT '0' COMMENT '过期时间',
                    `issueTime` int(10) DEFAULT '0' COMMENT '发放时间',
                    `createdTime` INT(10) unsigned DEFAULT '0' COMMENT '创建时间',
                    `updatedTime` INT(10) unsigned DEFAULT '0'  COMMENT '更新时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='证书记录表';
            ");
        }

        return 1;
    }

    public function addUserColumn()
    {
        $this->logger('info', 'user创建passwordChanged字段');

        if (!$this->isFieldExist('user', 'passwordChanged')) {
            $this->getConnection()->exec("ALTER TABLE `user` ADD `passwordChanged` tinyint(1) NOT NULL default 0 COMMENT '是否修改密码';");
        }

        return 1;
    }

    public function downloadFont()
    {
        $filename = 'SourceHanSerifCNBold.otf';
        $directory = $this->biz['root_directory'].'/web/assets/fonts/';
        $filepath = $directory.$filename;
        if(file_exists($filepath)){
            return  1;
        }

        $this->download($this->fontDownloadUrl, $filepath);

        return 1;
    }

    public function addCheckCertificateJob()
    {
        if ($this->isJobExist('CheckCertificateExpireJob')) {
            return 1;
        }

        $currentTime = time();
        $this->getConnection()->exec("INSERT INTO `biz_scheduler_job` (
                `name`,
                `expression`,
                `class`,
                `args`,
                `priority`,
                `next_fire_time`,
                `misfire_threshold`,
                `misfire_policy`,
                `enabled`,
                `creator_id`,
                `updated_time`,
                `created_time`
            ) VALUES
            (
                'CheckCertificateExpireJob',
                '30 0 * * * ',
                'Biz\\\\Certificate\\\\Job\\\\CheckCertificateExpireJob',
                '',
                '100',
                '{$currentTime}',
                '300',
                'missed',
                '1',
                '0',
                '{$currentTime}',
                '{$currentTime}'
            );");
        $this->logger('info', '增加定时任务CheckCertificateExpireJob');
        return 1;
    }

    protected function download($url, $filepath)
    {
        $fp = fopen($filepath, 'w');
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_exec($curl);
        curl_close($curl);

        fclose($fp);

        return $filepath;
    }

    private function getSettingService()
    {
        return new \Biz\System\Service\Impl\SettingServiceImpl($this->biz);
    }

    private function getCacheService()
    {
        return $this->biz->service('System:CacheService');
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
