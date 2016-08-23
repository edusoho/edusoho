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
        $this->registerOpenCourseSearch(); // 注册公开课云搜索
    }

    protected function registerOpenCourseSearch()
    {
        $search = $this->getSettingService()->get('cloud_search');

        $site = $this->getSettingService()->get('site');

        if (empty($site)) {
            throw new \RuntimeException('没有站点信息');
        }

        $host = $site['url'];
        if (strpos($host, 'http://') !== 0) {
            $host = 'http://' . $host;
        }
        $host = rtrim(rtrim($host), '/');

        $urls = array(
            array('category' => 'course', 'url' => $host . '/api/courses?cursor=0&start=0&limit=100'),
            array('category' => 'lesson', 'url' => $host . '/api/lessons?cursor=0&start=0&limit=100'),
            array('category' => 'user', 'url' => $host . '/api/users?cursor=0&start=0&limit=100'),
            array('category' => 'thread', 'url' => $host . '/api/chaos_threads?cursor=0,0,0&start=0,0,0&limit=50'),
            array('category' => 'article', 'url' => $host . '/api/articles?cursor=0&start=0&limit=100'),
            array('category' => 'openCourse', 'url' => $host . '/api/open_courses?cursor=0&start=0&limit=100'),
            array('category' => 'openLesson', 'url' => $host . '/api/open_course_lessons?cursor=0&start=0&limit=100'),
        );

        $remote = CloudAPIFactory::create();
        $ret    = $remote->post('/search/accounts/me', array(
            'urls' => urlencode(json_encode($urls)),
        ));
        
        if (!(bool)$ret['success']) {
            throw new \RuntimeException('注册公开课云搜索失败');
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
