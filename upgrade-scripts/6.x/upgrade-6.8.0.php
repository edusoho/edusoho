<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();

            $this->updateCrontabSetting();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir') . "/../web/install");
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

        if ($this->isTableExist('money_card_batch') && !$this->isFieldExist('money_card_batch', 'token')) {
            $connection->exec("ALTER TABLE `money_card_batch` ADD `token` varchar(64) NOT NULL DEFAULT '0'  AFTER `rechargedNumber`;");
        }

        if ($this->isTableExist('money_card')) {
            $connection->exec("ALTER TABLE `money_card` CHANGE `cardStatus` `cardStatus` ENUM('normal','invalid','recharged','receive') NOT NULL DEFAULT 'invalid';");
            if (!$this->isFieldExist('money_card', 'receiveTime')) {
                $connection->exec("ALTER TABLE `money_card` ADD `receiveTime` int(10) NOT NULL DEFAULT '0' COMMENT '领取学习卡时间' AFTER `cardStatus`; ");
            }
        }

        if (!$this->isTableExist('recent_post_num')) {
            $connection->exec(
                "CREATE TABLE `recent_post_num` (
                 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
                 `ip` varchar(20) NOT NULL COMMENT 'IP',
                 `type` varchar(255) NOT NULL COMMENT '类型',
                 `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'post次数',
                 `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
                 `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';"
            );
        }

        $setting = array(
            'rules' => array(
                'thread' => array(
                    'fiveMuniteRule' => array(
                        'interval' => 300,
                        'postNum' => 100,
                    ),
                ),
                'threadLoginedUser' => array(
                    'fiveMuniteRule' => array(
                        'interval' => 300,
                        'postNum' => 50,
                    ),
                ),
            ),
        );
        ServiceKernel::instance()->createService('System.SettingService')->set('post_num_rules', $setting);
    }

    private function updateCrontabSetting()
    {
        $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir') . "/../app/data/crontab_config.yml");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
        }
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
