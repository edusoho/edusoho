<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

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
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir') . "../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();

        $connection->exec("update course_member cm1 LEFT JOIN classroom_member cm2 ON cm1.classroomId = cm2.classroomId and cm1.userId=cm2.userId and cm1.joinedType='classroom' set cm1.levelId=cm2.levelId where cm1.joinedType='classroom'");

        $setting = $this->getSettingService()->get('user_partner');
        if(!empty($setting['mode']) && $setting['mode'] == 'phpwind') {
            $setting['mode'] = 'default';
            $setting = $this->getSettingService()->set('user_partner', $setting);
        }

        $connection->exec("ALTER TABLE `ip_blacklist` MODIFY `type`  enum('failed','banned') NOT NULL DEFAULT 'failed' COMMENT '禁用类型'");

        $this->updateUserCenterConfig();
    }


    protected function updateUserCenterConfig()
    {
        $discuzConfigPath = ServiceKernel::instance()->getParameter('kernel.root_dir') . '/config/uc_client_config.php';

        $setting = $this->getSettingService()->get('user_partner');

        if(empty($setting)){
            return;
        }

        $setting['partner_config'] = array(
            'discuz'  => array(),
            'phpwind' => array(
                'conf'     => array(),
                'database' => array()
            )
        );

        if(file_exists($discuzConfigPath)){
            require $discuzConfigPath;
            $keys = array('uc_connect', 'uc_dbhost', 'uc_dbuser', 'uc_dbpw', 'uc_dbname', 'uc_dbcharset', 'uc_dbtablepre', 'uc_dbconnect', 'uc_key', 'uc_api', 'uc_charset', 'uc_ip', 'uc_appid', 'uc_ppp');
            foreach($keys as $key){
                if(defined(strtoupper($key))){
                    $setting['partner_config']['discuz'][$key] = constant(strtoupper($key));
                }
            }

            $this->getSettingService()->set('user_partner', $setting);
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

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql    = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * @return \Topxia\Service\System\Impl\SettingServiceImpl
     */
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

    /**
     * @return \Topxia\Service\Common\Connection
     */
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
