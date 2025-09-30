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
            $result = $this->updateScheme((int)$index);
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

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;
        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = [
            'createbizContract',
            'createbizContractGoodsRelation',
            'createbizContractSnapshot',
            'createbizContractSignRecord',
	];
        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }
        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');

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

    protected function createbizContract()
    {
        if (!$this->isTableExist('contract')) {
            $this->logger('info', '开始创建表Contract');
            $this->getConnection()->exec("
              CREATE TABLE IF NOT EXISTS `contract` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL COMMENT '合同名称',
                  `content` mediumtext COMMENT '合同内容',
                  `seal` varchar(255) NOT NULL COMMENT '甲方印章图标',
                  `sign` varchar(255) NOT NULL COMMENT '乙方签署内容',
                  `createdUserId` int(10) UNSIGNED NOT NULL COMMENT '创建人',
                  `updatedUserId` int(10) UNSIGNED NOT NULL COMMENT '更新人',
                  `createdTime` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) UNSIGNED NOT NULL COMMENT '最后更新时间',
                  PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = '电子合同表';
        ");
            $this->logger('info', '创建表Contract成功');
        }

        return 1;
    }

    protected function createbizContractGoodsRelation()
    {
        if (!$this->isTableExist('contract_goods_relation')) {
            $this->logger('info', '开始创建表contract_goods_relation');
            $this->getConnection()->exec("
              CREATE TABLE IF NOT EXISTS `contract_goods_relation` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `goodsKey` varchar(32) NOT NULL COMMENT '商品类型(course,classroom,itemBankExercise)_对应商品id',
                  `contractId` int(10) UNSIGNED NOT NULL COMMENT '合同id',
                  `sign` tinyint(1) NOT NULL COMMENT '签署要求 0: 非强制, 1: 强制',
                  `createdTime` int(10) UNSIGNED NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `contractId` (`contractId`),
                  KEY `goodsKey` (`goodsKey`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = '商品合同关系表';
        ");
            $this->logger('info', '创建表contract_goods_relation成功');
        }

        return 1;
    }

    protected function createbizContractSnapshot()
    {
        if (!$this->isTableExist('contract_snapshot')) {
            $this->logger('info', '开始创建表contract_snapshot');
            $this->getConnection()->exec("
              CREATE TABLE IF NOT EXISTS `contract_snapshot` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL COMMENT '合同名称',
                  `content` mediumtext COMMENT '合同内容',
                  `seal` varchar(255) NOT NULL COMMENT '甲方印章图标',
                  `version` varchar(32) NOT NULL COMMENT '版本号(MD5)',
                  `createdTime` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `version` (`version`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = '合同快照表';
        ");
            $this->logger('info', '创建表contract_snapshot成功');
        }

        return 1;
    }

    protected function createbizContractSignRecord()
    {
        if (!$this->isTableExist('contract_sign_record')) {
            $this->logger('info', '开始创建表contract_sign_record');
            $this->getConnection()->exec("
              CREATE TABLE IF NOT EXISTS `contract_sign_record` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `userId` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
                  `goodsKey` varchar(32) NOT NULL COMMENT '商品类型(course,classroom,itemBankExercise)_对应商品id',
                  `snapshot` varchar(1024) COMMENT '签署快照',
                  `createdTime` int(10) UNSIGNED NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `userId` (`userId`),
                  KEY `goodsKey` (`goodsKey`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = '合同签署记录表';
        ");
            $this->logger('info', '创建表contract_sign_record成功');
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

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return !empty($result);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return !empty($result);
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return !empty($result);
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform:CloudAppLogDao');
    }
}
