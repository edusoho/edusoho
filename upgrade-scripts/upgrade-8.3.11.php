<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use QiQiuYun\SDK\Auth;
use QiQiuYun\SDK\HttpClient\Client;

class EduSohoUpgrade extends AbstractUpdater
{
    private $userUpdateHelper = null;

    public function __construct($biz)
    {
        parent::__construct($biz);

        $this->userUpdateHelper = new BatchUpdateHelper($this->getUserDao());
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
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'addTableIndex',
            'addInvoiceTable',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if ($index == 0) {
            $this->logger('info', '开始执行升级脚本');
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

    public function addInvoiceTable()
    {
        if (!$this->isTableExist('biz_invoice')) {
            $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `biz_invoice`(
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `sn` varchar(64) NOT NULL COMMENT '申请开票号',
              `title` varchar(255) NOT NULL DEFAULT '' COMMENT '发票抬头',
              `type` enum('electronic', 'paper', 'vat') NOT NULL COMMENT '发票类型',
              `taxpayer_identity` varchar(255) NOT NULL DEFAULT '' COMMENT '纳税人识别号',
              `content` varchar(100) NOT NULL DEFAULT '' COMMENT '发票内容',
              `comment` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
              `address` varchar(255) DEFAULT NULL COMMENT '邮寄地址',
              `phone` varchar(255) NOT NULL DEFAULT '' COMMENT '联系电话',
              `company_mobile` varchar(20) DEFAULT NULL COMMENT '公司电话',
              `company_address` varchar(255) DEFAULT NULL COMMENT '公司地址',
              `email` varchar(255) NOT NULL DEFAULT '' COMMENT '电子邮箱',
              `receiver` varchar(100) NOT NULL DEFAULT '' COMMENT '收件人',
              `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户Id',
              `status` enum('unchecked', 'sent', 'refused') NOT NULL DEFAULT 'unchecked' COMMENT '申请状态',
              `money` bigint(16) NOT NULL DEFAULT 0 COMMENT '开票金额',
              `review_user_id` int(11) DEFAULT 0 COMMENT '审核人Id',
              `bank` varchar(255) DEFAULT NULL COMMENT '开户行',
              `account` varchar(255) DEFAULT NULL COMMENT '开户行账号',
              `number` varchar(64) DEFAULT '' COMMENT '发票号',
              `post_name` VARCHAR(20) NULL DEFAULT NULL COMMENT '快递名称',
              `post_number` varchar(64) DEFAULT '' COMMENT '邮寄号',
              `refuse_comment` varchar(255) DEFAULT '' COMMENT '拒绝备注',
              `created_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
              `updated_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
          ");
        }

        if (!$this->isTableExist('biz_invoice')) {
            $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `biz_invoice_template`(
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `title` varchar(255) NOT NULL DEFAULT '' COMMENT '发票抬头',
              `type` enum('electronic', 'paper', 'vat') NOT NULL COMMENT '发票类型',
              `taxpayer_identity` varchar(255) NOT NULL DEFAULT '' COMMENT '纳税人识别号',
              `content` varchar(100) NOT NULL DEFAULT '培训费' COMMENT '发票内容',
              `comment` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
              `email` varchar(255) NOT NULL DEFAULT '' COMMENT '电子邮箱',
              `address` varchar(255) DEFAULT NULL COMMENT '邮寄地址',
              `company_address` varchar(255) DEFAULT NULL COMMENT '公司地址',
              `bank` varchar(255) DEFAULT NULL COMMENT '开户行',
              `account` varchar(255) DEFAULT NULL COMMENT '开户行账号',
              `company_mobile` varchar(20) DEFAULT NULL COMMENT '公司电话',
              `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
              `receiver` varchar(100) NOT NULL DEFAULT '' COMMENT '收件人',
              `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户Id',
              `created_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
              `updated_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
              `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('biz_pay_trade', 'invoice_sn')) {
            $this->getConnection()->exec("
           ALTER TABLE `biz_pay_trade` ADD COLUMN `invoice_sn` varchar(64) default '0' COMMENT '申请开票sn'
           ");
        }

        if ($this->isFieldExist('biz_order', 'invoice_sn')) {
            $this->getConnection()->exec("
           ALTER TABLE `biz_order` DROP `invoice_sn`
           ");
        }

        return 1;
    }

    public function addTableIndex()
    {
        if ($this->isJobExist('AddTableIndexJob')) {
            return 1;
        }

        $currentTime = time();
        $today = strtotime(date('Y-m-d', $currentTime).'02:00:00');

        if ($currentTime > $today) {
            $time = strtotime(date('Y-m-d', strtotime('+1 day')).'02:00:00');
        }

        $this->getConnection()->exec("INSERT INTO `biz_scheduler_job` (
              `name`,
              `expression`,
              `class`,
              `args`,
              `priority`,
              `pre_fire_time`,
              `next_fire_time`,
              `misfire_threshold`,
              `misfire_policy`,
              `enabled`,
              `creator_id`,
              `updated_time`,
              `created_time`
        ) VALUES (
              'AddTableIndexJob',
              '',
              'Biz\\\\TableIndex\\\\\Job\\\\AddTableIndexJob',
              '',
              '200',
              '0',
              '{$time}',
              '300',
              'executing',
              '1',
              '0',
              '{$currentTime}',
              '{$currentTime}'
        )");
        $this->logger('info', 'INSERT增加索引的定时任务AddTableIndexJob');
        return 1;
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
     * @return \Biz\TableIndex\Service\TableIndexService
     */
    protected function getTableIndexService()
    {
        return $this->createService('TableIndex:TableIndexService');
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

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return \Biz\Course\Dao\CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
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
}
