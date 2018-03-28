<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;
    private $userUpdateHelper = null;
    private $batchUUIDs = array();

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
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
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

    private function updateScheme($index)
    {
        $definedFuncNames = array(
           'addCourseSetColumn',
           'addCourseMemberIndex',
           'setCourseSort',
           'addClassroomColumn',
           'addClassroomMemberIndex',
           'setClassroomSort',
           'registerCrontab',
           'updatePaymentsSetting',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key+1] = $funcName;
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

    protected function addCourseSetColumn()
    {
        if (!$this->isFieldExist('course_set_v8', 'hotSeq')) {
            $this->getConnection()->exec("ALTER TABLE `course_set_v8` ADD `hotSeq` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最热排序' AFTER `studentNum`;");
        }
        
        $this->logger('info', 'course_set_v8新增字段hotSeq完成');

        return 1;
    }

    protected function addCourseMemberIndex()
    {
        $this->createIndex('course_member', 'courseSetId', 'courseSetId');

        $this->logger('info', 'course_member新增索引courseSetId完成');
        return 1;
    }

    protected function setCourseSort()
    {
        $courseSetting = $this->getSettingService()->get('course', array());
        if (isset($courseSetting['explore_default_orderBy']) && $courseSetting['explore_default_orderBy'] == 'studentNum') {
            $courseSetting['explore_default_orderBy'] = 'hotSeq';
            $this->getSettingService()->set('course', $courseSetting);

            $this->logger('info', '更新课程列表页默认排序');
        }
        
        return 1;
    }

    protected function addClassroomColumn()
    {
        if (!$this->isFieldExist('classroom', 'hotSeq')) {
            $this->getConnection()->exec("ALTER TABLE `classroom` ADD `hotSeq` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最热排序' AFTER `ratingNum`;");
        }
        
        $this->logger('info', 'classroom新增字段hotSeq完成');
        return 1;
    }

    protected function addClassroomMemberIndex()
    {
        $this->createIndex('classroom_member', 'classroomId', 'classroomId');

        $this->logger('info', 'classroom_member新增索引classroomId完成');
        return 1;
    }

    protected function setClassroomSort()
    {
        $classroomSetting = $this->getSettingService()->get('classroom', array());
        if (isset($classroomSetting['explore_default_orderBy']) && $classroomSetting['explore_default_orderBy'] == 'studentNum') {
            $classroomSetting['explore_default_orderBy'] = 'hotSeq';
            $this->getSettingService()->set('classroom', $classroomSetting);

            $this->logger('info', '更新班级列表页默认排序');
        }
        
        return 1;
    }

    protected function registerCrontab()
    {
        if (!$this->isJobExist('updateCourseSetHotSeq')) {
            $jobFields = array(
                'name' => 'updateCourseSetHotSeq',
                'expression' => '47 4 * * *',
                'class' => 'Biz\Course\Job\UpdateCourseSetHotSeqJob',
                'misfire_threshold' => 0,
                'args' => array(),
            );

            $this->getSchedulerService()->register($jobFields);
        }

        $this->logger('info', '新增updateHotSeq的定时任务完成');
        return 1;
    }

    protected function updatePaymentsSetting()
    {
        $paymentSetting = $this->getSettingService()->get('payment');
        $paymentEnabled = 0;
        $defaultPayments = array(
            'alipay_enabled',
            'wxpay_enabled',
            'llpay_enabled',
            'heepay_enabled',
            'quickpay_enabled',
            'llpay_enabled',
        );
        foreach ($defaultPayments as $defaultPayment) {
            if ($paymentSetting[$defaultPayment]) {
                $paymentEnabled = 1;
            }
        }
        if (isset($paymentSetting['disabled_message'])) {
            unset($paymentSetting['disabled_message']);
        }
        $paymentSetting['enabled'] = $paymentEnabled;
        $this->getSettingService()->set('payment', $paymentSetting);
        
        $this->logger('info', 'payment设置更新完成');

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
