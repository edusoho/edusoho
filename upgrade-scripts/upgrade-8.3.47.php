<?php

use Biz\System\Service\SettingService;
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
            'createCouponBatch',
            'createPromotedCouponBatch',
            'createPromoteCouponBatch',
            'updateInviteSetting',
            'initCouponTargetIds',
            'initCouponBatchTargetIds',
            'updateCouponConfig',
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

    public function createCouponBatch()
    {
        if (!$this->isTableExist('coupon_batch')) {
            $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `coupon_batch` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(64) NOT NULL COMMENT '批次名称',
              `token` varchar(64) NOT NULL DEFAULT '0',
              `type` enum('minus','discount') NOT NULL COMMENT '优惠方式',
              `generatedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '生成数',
              `usedNum` int(11) NOT NULL DEFAULT '0' COMMENT '使用次数',
              `rate` float(10,2) unsigned NOT NULL COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
              `prefix` varchar(64) NOT NULL COMMENT '批次前缀',
              `digits` int(20) unsigned NOT NULL COMMENT '优惠码位数',
              `money` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已优惠金额',
              `deadline` int(10) unsigned NOT NULL COMMENT '失效时间',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '使用对象类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0',
              `description` text COMMENT '优惠说明',
              `createdTime` int(10) unsigned NOT NULL,
              `fullDiscountPrice` float(10,2) unsigned DEFAULT NULL,
              `h5MpsEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '通过商品详情页小程序/微网校渠道发放',
              `linkEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '通过链接渠道发放',
              `codeEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '通过优惠码渠道发放',
              `unreceivedNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '未领取的数量',
              `deadlineMode` enum('time','day') NOT NULL DEFAULT 'time' COMMENT '有效期模式',
              `fixedDay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '固定天数',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='优惠码批次表';
        ");
        }

        if (!$this->isFieldExist('coupon_batch', 'h5MpsEnable')) {
            $this->getConnection()->exec("ALTER TABLE `coupon_batch` ADD `h5MpsEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '通过商品详情页小程序/微网校渠道发放' AFTER `fullDiscountPrice`;");
        }

        if (!$this->isFieldExist('coupon_batch', 'linkEnable')) {
            $this->getConnection()->exec("ALTER TABLE `coupon_batch` ADD `linkEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '通过链接渠道发放' AFTER `fullDiscountPrice`;");
        }

        if (!$this->isFieldExist('coupon_batch', 'codeEnable')) {
            $this->getConnection()->exec("ALTER TABLE `coupon_batch` ADD `codeEnable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '通过优惠码渠道发放' AFTER `fullDiscountPrice`;");
        }

        if (!$this->isFieldExist('coupon_batch', 'unreceivedNum')) {
            $this->getConnection()->exec("ALTER TABLE `coupon_batch` ADD `unreceivedNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '未领取的数量' AFTER `fullDiscountPrice`;");

            $this->getConnection()->exec("UPDATE coupon_batch cb SET cb.unreceivedNum=cb.generatedNum-(SELECT COUNT(*) FROM coupon c WHERE c.batchid=cb.id AND c.`status`!='unused');");
        }

        if (!$this->isFieldExist('coupon_batch', 'deadlineMode')) {
            $this->getConnection()->exec("ALTER TABLE `coupon_batch` ADD `deadlineMode` enum('time','day') NOT NULL DEFAULT 'time' COMMENT '有效期模式' AFTER `money`;");
        }

        if (!$this->isFieldExist('coupon_batch', 'fixedDay')) {
            $this->getConnection()->exec("ALTER TABLE `coupon_batch` ADD `fixedDay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '固定天数' AFTER `deadline`;");
        }

        if (!$this->isFieldExist('coupon_batch', 'targetIds')) {
            $this->getConnection()->exec("ALTER TABLE `coupon_batch` ADD `targetIds` text COMMENT '优惠券批次绑定资源' AFTER `targetId`;");
        }

        if (!$this->isFieldExist('coupon', 'targetIds')) {
            $this->getConnection()->exec("ALTER TABLE `coupon` ADD `targetIds` text COMMENT '优惠券绑定资源' AFTER `targetId`;");
        }

        return 1;
    }

    /**
     * 根据邀请注册（被邀请人注册奖励）配置创建优惠券批次并生成10000张优惠券
     */
    public function createPromotedCouponBatch()
    {
        $setting = $this->getSettingService()->get('invite', array());
        if (isset($setting['promoted_user_value']) && $setting['promoted_user_value'] > 0 && !empty($setting['deadline'])) {
            $batch = array(
                'token' => 0,
                'name' => '被邀请人注册奖励',
                'type' => 'minus',
                'generatedNum' => 10000,
                'usedNum' => 0,
                'rate' => $setting['promoted_user_value'],
                'prefix' => md5('upgradeCreatePromotedCouponBatch'),
                'digits' => 8,
                'money' => 0,
                'deadlineMode' => 'day',
                'deadline' => 0,
                'fixedDay' => $setting['deadline'],
                'targetType' => 'all',
                'targetId' => 0,
                'description' => '被邀请人注册奖励',
                'createdTime' => time(),
                'fullDiscountPrice' => 0,
                'unreceivedNum' => 0,
                'codeEnable' => 0,
                'linkEnable' => 0,
                'h5MpsEnable' => 0,
            );
            $promotedBatch = $this->getCouponBatchDao()->create($batch);
            for ($i = 0; $i < 10; ++$i) {
                $this->getCouponBatchService()->createBatchCoupons($promotedBatch['id'], 1000);
            }
        }

        return 1;
    }

    /**
     * 根据邀请注册（邀请人注册奖励）配置创建优惠券批次并生成10000张优惠券
     */
    public function createPromoteCouponBatch()
    {
        $setting = $this->getSettingService()->get('invite', array());
        if (isset($setting['promote_user_value']) && $setting['promote_user_value'] > 0 && !empty($setting['deadline'])) {
            $batch = array(
                'token' => 0,
                'name' => '邀请人注册奖励',
                'type' => 'minus',
                'generatedNum' => 10000,
                'usedNum' => 0,
                'rate' => $setting['promote_user_value'],
                'prefix' => md5('upgradeCreatePromoteCouponBatch'),
                'digits' => 8,
                'money' => 0,
                'deadlineMode' => 'day',
                'deadline' => 0,
                'fixedDay' => $setting['deadline'],
                'targetType' => 'all',
                'targetId' => 0,
                'description' => '邀请人注册奖励',
                'createdTime' => time(),
                'fullDiscountPrice' => 0,
                'unreceivedNum' => 0,
                'codeEnable' => 0,
                'linkEnable' => 0,
                'h5MpsEnable' => 0,
            );
            $promoteBatch = $this->getCouponBatchDao()->create($batch);
            for ($i = 0; $i < 10; ++$i) {
                $this->getCouponBatchService()->createBatchCoupons($promoteBatch['id'], 1000);
            }
        }

        return 1;
    }

    /**
     * 根据原有邀请注册配置修改参数
     */
    public function updateInviteSetting()
    {
        $setting = $this->getSettingService()->get('invite', array());
        if (!empty($setting) && !isset($setting['promoted_user_batchId'])) {
            $promotedMd5Code = md5('upgradeCreatePromotedCouponBatch');
            $batchSql = "select id from `coupon_batch` where prefix= '{$promotedMd5Code}'";
            $promotedBatch = $this->getConnection()->fetchAssoc($batchSql);
            $promoteMd5Code = md5('upgradeCreatePromoteCouponBatch');
            $batchSql = "select id from `coupon_batch` where prefix= '{$promoteMd5Code}'";
            $promoteBatch = $this->getConnection()->fetchAssoc($batchSql);

            $default = array(
                'invite_code_setting' => empty($setting['invite_code_setting']) ? 0 : $setting['invite_code_setting'],
                'promoted_user_enable' => empty($setting['promoted_user_value']) ? 0 : 1,
                'promoted_user_batchId' => empty($promotedBatch) ? 0 : $promotedBatch['id'],
                'promote_user_enable' => empty($setting['promote_user_value']) ? 0 : 1,
                'promote_user_batchId' => empty($promoteBatch) ? 0 : $promoteBatch['id'],
                'get_coupon_setting' => empty($setting['get_coupon_setting']) ? 0 : 1,
                'inviteInfomation_template' => empty($setting['inviteInfomation_template']) ? '' : $setting['inviteInfomation_template'],
                'remain_number' => '',
                'mobile' => '',
            );
            $this->getSettingService()->set('invite', $default);
        }

        return 1;
    }

    /**
     * 初始化coupon的targetIds字段数据
     */
    public function initCouponTargetIds()
    {
        $couponSql = "update `coupon` set targetIds = CONCAT('|',targetId,'|') where targetId > 0 and targetIds is null and targetType != 'vip'";
        $this->getConnection()->exec($couponSql);

        return 1;
    }

    /**
     * 初始化coupon_batch的targetIds字段数据
     */
    public function initCouponBatchTargetIds()
    {
        $couponSql = "update `coupon_batch` set targetIds = CONCAT('|',targetId,'|') where targetId > 0 and targetIds is null and targetType != 'vip'";
        $this->getConnection()->exec($couponSql);

        return 1;
    }

    /**
     * 在优惠券插件已安装的情况下，主程序menus_admin.yml和routing_admin.yml会覆盖主程序，备份并清空两个文件
     */
    public function updateCouponConfig()
    {
        $pluginPath = $this->biz['plugin.directory'];
        $menusPath = $pluginPath.'/CouponPlugin/Resources/config/menus_admin.yml';
        $menusDistPath = $menusPath.'.dist';
        $routingPath = $pluginPath.'/CouponPlugin/Resources/config/routing_admin.yml';
        $routingDistPath = $routingPath.'.dist';
        $filesystem = new Filesystem();
        if ($filesystem->exists($menusPath)) {
            if (!$filesystem->exists($menusDistPath)) {
                $filesystem->copy($menusPath, $menusDistPath);
            }
            file_put_contents($menusPath, '');
        }

        if ($filesystem->exists($routingPath)) {
            if (!$filesystem->exists($routingDistPath)) {
                $filesystem->copy($routingPath, $routingDistPath);
            }
            file_put_contents($routingPath, '');
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

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    private function makeUUID()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    /**
     * @return SettingService
     */
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

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return \Biz\Coupon\Service\CouponBatchService
     */
    protected function getCouponBatchService()
    {
        return $this->createService('Coupon:CouponBatchService');
    }

    /**
     * @return \Biz\Coupon\Service\CouponService
     */
    protected function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }

    /**
     * @return \Biz\Coupon\Dao\CouponBatchDao
     */
    protected function getCouponBatchDao()
    {
        return $this->createDao('Coupon:CouponBatchDao');
    }

    /**
     * @return \Biz\Coupon\Dao\CouponDao
     */
    protected function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
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
