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

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
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
            'userAddShowable',
            'updateConsultPhoneSetting',
            'groupAddRecommended',
        );

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

    protected function userAddShowable()
    {
        if (!$this->isFieldExist('user', 'showable')) {
            $sql = "ALTER TABLE `user` ADD COLUMN `showable` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '在网校显示';";
            $this->getConnection()->exec($sql);
        }

        return 1;
    }

    protected function groupAddRecommended()
    {
        $sql = '';
        if (!$this->isFieldExist('groups', 'recommended')) {
            $sql .= "ALTER TABLE `groups` ADD COLUMN `recommended` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐' after `ownerId`;";
        }
        if (!$this->isFieldExist('groups', 'recommendedSeq')) {
            $sql .= "ALTER TABLE `groups` ADD COLUMN `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号' after `recommended`;";
        }
        if (!$this->isFieldExist('groups', 'recommendedTime')) {
            $sql .= "ALTER TABLE `groups` ADD COLUMN `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间' after `recommendedSeq`;";
        }
        if ($sql) {
            $this->getConnection()->exec($sql);
        }

        return 1;
    }

    protected function updateConsultPhoneSetting()
    {
        $consult = $this->getSettingService()->get('consult', []);
        if (isset($consult['enabled']) && (int)$consult['enabled']) {
            $notSetPhone = 4008041114;
            $conditions = [
                'roles' => '|ROLE_SUPER_ADMIN|',
                'locked' => 0,
                'excludeVerifiedMobile' => '',
            ];
            $count = $this->getUserDao()->count($conditions);
            $supperAdminMobile = $this->getUserDao()->search($conditions, [], 0, $count, ['verifiedMobile']);
            $supperAdminMobile = array_column($supperAdminMobile, 'verifiedMobile');
            $phone = reset($supperAdminMobile);
            $update = false;
            foreach ($consult['phone'] as &$item) {
                $itemName = preg_replace('/[^\d]/', '', $item['name']);
                $itemNumber = preg_replace('/[^\d]/', '', $item['number']);
                if ($notSetPhone == $itemName) {
                    $update = true;
                    $item['name'] = $phone ?: '';
                }
                if ($notSetPhone == $itemNumber) {
                    $update = true;
                    $item['number'] = $phone ?: '';
                }
            }
            if ($update) {
                $this->getSettingService()->set('consult', $consult);
            }
        }

        return 1;
    }

    /**
     * @return \Biz\User\Dao\UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
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

    /**
     * @return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
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
}
