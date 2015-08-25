<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

class EduSohoUpgrade extends AbstractUpdater
{

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $index = $this->batchDownload($index);
            if (!empty($index)) {
                return $index;
            }
            $this->vendorExtract();
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
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
        return $index;
    }

    private function batchDownload($index)
    {
        if ($index < 19) {
            $progress = 38/19;
            $filepath = 'http://try6.edusoho.cn/vendor-'.$index.'.zip';
            $curl = curl_init($filepath);
            $targetPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/vendor-'.$index.'.zip';
            if (!file_exists($targetPath)) {
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                $file = curl_exec($curl);
                curl_close($curl);
                $tp = @fopen($targetPath, 'a');
                fwrite($tp, $file);
                fclose($tp);
            }
            $index++;
            return array(
                'index' => $index,
                'message' => '下载大型文件'.$index.'/19',
                'progress' => $progress
            );
        }
        return 0;
    }

    private function vendorExtract()
    {
        exec('mkdir '.ServiceKernel::instance()->getParameter('kernel.root_dir').'/../vendor_v2');
        for ($i=0; $i < 19; $i++) { 
            $zip = new \ZipArchive;
            $filepath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/vendor-'.$i.'.zip';
            $unzipDir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../vendor_v2';
            if ($zip->open($filepath) === TRUE) {
                $zip->extractTo($unzipDir);
                $zip->close();
                $filesystem = new Filesystem();
                $filesystem->remove($filepath);
            } else {
                throw new \Exception('无法解压缩安装包！');
            }
        }
        $autoFilePath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/autoload.php';
        $content = file_get_contents($autoFilePath);
        $content = str_replace('/vendor/', '/vendor_v2/', $content);
        file_put_contents($autoFilePath, $content);
    }
    
    private function updateScheme()
    {
        $connection = $this->getConnection();
        $connection->exec("ALTER TABLE `classroom_member` CHANGE `role` `role` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'auditor' COMMENT '角色'");
        if (!$this->isFieldExist('classroom', 'assistantIds')) {
            $connection->exec("ALTER TABLE `classroom` ADD `assistantIds` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '助教Ids' AFTER `teacherIds`");
        }
        $connection->exec("UPDATE `classroom_member` SET `role` = concat('|',role,'|') WHERE `role` NOT LIKE '%|%'");
        $connection->exec("UPDATE `classroom_member` SET `role` = '|assistant|student|' WHERE `role` = '|studentAssistant|'");
        $connection->exec("UPDATE user_approval set `status` = 'approving' where `status` = 'approved'");
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

    
    
    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
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
