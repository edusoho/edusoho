<?php

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    private $perPageCount = 10000;

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
            'updateSetting',
            'addJoinedChannel',
            'updateCourseMemberJoinedChannel',
            'updateClassroomMemberJoinedChannel',
            'downloadPlugin',
            'updatePlugin',
            'updateThemeConfig',
            'updateUserVipRight',
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

    public function updateSetting()
    {
        $courseSetting = $this->getSettingService()->get('course', []);
        $classroomSetting = $this->getSettingService()->get('classroom', []);
        $messageSetting = $this->getSettingService()->get('message', []);
        $openCourseSetting = $this->getSettingService()->get('openCourse', []);
        $articleSetting = $this->getSettingService()->get('article', []);

        $ugcReviewSetting = [
            'enable_review' => '1',
            'enable_course_review' => isset($courseSetting['show_review']) ? $courseSetting['show_review'] : '1',
            'enable_classroom_review' => isset($classroomSetting['show_review']) ? $classroomSetting['show_review'] : '1',
            'enable_question_bank_review' => '1',
            'enable_open_course_review' => isset($openCourseSetting['show_comment']) ? $openCourseSetting['show_comment'] : '1',
            'enable_article_review' => isset($articleSetting['show_comment']) ? $articleSetting['show_comment'] : '1',
        ];
        $ugcNoteSetting = [
            'enable_note' => '1',
            'enable_course_note' => isset($courseSetting['show_note']) ? $courseSetting['show_note'] : '1',
            'enable_classroom_note' => isset($classroomSetting['show_review']) ? $classroomSetting['show_review'] : '1',
        ];
        $ugcThreadSetting = [
            'enable_thread' => '1',
            'enable_course_question' => isset($courseSetting['show_question']) ? $courseSetting['show_question'] : '1',
            'enable_classroom_question' => isset($classroomSetting['show_thread']) ? $classroomSetting['show_thread'] : '1',
            'enable_course_thread' => isset($courseSetting['show_discussion']) ? $courseSetting['show_discussion'] : '1',
            'enable_classroom_thread' => isset($classroomSetting['show_thread']) ? $classroomSetting['show_thread'] : '1',
            'enable_group_thread' => '1',
        ];
        $ugcPrivateMessageSetting = [
            'enable_private_message' => isset($messageSetting['showable']) ? $messageSetting['showable'] : '1',
            'student_to_student' => isset($messageSetting['studentToStudent']) ? $messageSetting['studentToStudent'] : '1',
            'student_to_teacher' => isset($messageSetting['studentToTeacher']) ? $messageSetting['studentToTeacher'] : '1',
            'teacher_to_student' => isset($messageSetting['teacherToStudent']) ? $messageSetting['teacherToStudent'] : '1',
        ];
        if (empty($this->getSettingService()->get('ugc_review'))) {
            $this->getSettingService()->set('ugc_review', $ugcReviewSetting);
        }
        if (empty($this->getSettingService()->get('ugc_note'))) {
            $this->getSettingService()->set('ugc_note', $ugcNoteSetting);
        }
        if (empty($this->getSettingService()->get('ugc_thread'))) {
            $this->getSettingService()->set('ugc_thread', $ugcThreadSetting);
        }
        if (empty($this->getSettingService()->get('ugc_private_message'))) {
            $this->getSettingService()->set('ugc_private_message', $ugcPrivateMessageSetting);
        }

        $siteSetting = $this->getSettingService()->get('site', []);
        $originLicenseSetting = $this->getSettingService()->get('license', []);


        $qualificationsSetting = [
            'icp' => isset($siteSetting['icp']) ? $siteSetting['icp'] : '',
            'icpUrl' => isset($siteSetting['icpUrl']) ? $siteSetting['icpUrl'] : 'https://beian.miit.gov.cn',
            'recordPicture' => isset($siteSetting['recordPicture']) ? $siteSetting['recordPicture'] : '',
            'recordCode' => isset($siteSetting['recordCode']) ? $siteSetting['recordCode'] : '',
            'recordUrl' => isset($siteSetting['recordUrl']) ? $siteSetting['recordUrl'] : 'http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=',
        ];

        if (empty($this->getSettingService()->get('qualifications'))){
            $this->getSettingService()->set('qualifications', $qualificationsSetting);
        }

        if (empty($this->getSettingService()->get('permits'))){
            $this->getSettingService()->set('permits', $originLicenseSetting);
        }

        return 1;
    }

    public function addJoinedChannel()
    {
        if (!$this->isFieldExist('course_member', 'joinedChannel')) {
            $this->getConnection()->exec("ALTER TABLE `course_member` ADD `joinedChannel` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '加入来源' AFTER `joinedType`;");
        }

        if (!$this->isFieldExist('classroom_member', 'joinedChannel')) {
            $this->getConnection()->exec("ALTER TABLE `classroom_member` ADD `joinedChannel` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '加入来源' AFTER `orderId`;");
        }

        return 1;
    }

    public function updateCourseMemberJoinedChannel($page)
    {
        $count = $this->getTableCount('course_member');
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $ids = $this->getConnection()->fetchAll("select id from course_member order by id ASC limit {$start},{$this->perPageCount};");
            if (!empty($ids)) {
                $ids = ArrayToolkit::column($ids, 'id');
                $ids = implode(',', $ids);
                $this->getConnection()->exec("
                    UPDATE course_member AS cm INNER JOIN member_operation_record mor 
                        ON cm.id = mor.member_id and mor.target_type = 'course' 
                        SET cm.joinedChannel=mor.reason_type where cm.id in ({$ids});
                ");
            }

            $this->logger('info', "更新course_member，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else {
            return 1;
        }
    }

    public function updateClassroomMemberJoinedChannel($page)
    {
        $count = $this->getTableCount('classroom_member');
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $ids = $this->getConnection()->fetchAll("select id from classroom_member order by id ASC limit {$start},{$this->perPageCount};");
            if (!empty($ids)) {
                $ids = ArrayToolkit::column($ids, 'id');
                $ids = implode(',', $ids);
                $this->getConnection()->exec("
                    UPDATE classroom_member AS cm INNER JOIN member_operation_record mor 
                        ON cm.id = mor.member_id and mor.target_type = 'classroom' 
                        SET cm.joinedChannel=mor.reason_type where cm.id in ({$ids});
                ");
            }

            $this->logger('info', "更新classroom_member，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else {
            return 1;
        }
    }

    protected function downloadPlugin($page)
    {
        $plugin = $this->getUpdatePluginInfo($page);
        if (empty($plugin)) {
            return 1;
        }

        $pluginCode = $plugin[0];
        $pluginPackageId = $plugin[1];

        $this->logger('warning', '检测是否安装'.$pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装'.$pluginCode);

            return $page + 1;
        }
        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if (isset($package['error'])) {
                $this->logger('warning', $package['error']);
                return $page + 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($pluginPackageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($pluginPackageId);
            $errors = array_merge($error1, $error2);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->logger('warning', $error);
                }
            };
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger('info', '检测完毕');
        return $page + 1;
    }

    protected function updatePlugin($page)
    {
        $plugin = $this->getUpdatePluginInfo($page);
        if (empty($plugin)) {
            return 1;
        }

        $pluginCode = $plugin[0];
        $pluginPackageId = $plugin[1];

        $this->logger('warning', '升级' . $pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装' . $pluginCode);

            return $page + 1;
        }

        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if (isset($package['error'])) {
                $this->logger('warning', $package['error']);
                return $page + 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($pluginPackageId, 'install', 0);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->logger('warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger('info', '升级完毕');
        return $page + 1;
    }

    private function getUpdatePluginInfo($page)
    {
        $pluginList = array(
            [
                'vip',
                2121
            ]
        );

        if (empty($pluginList[$page - 1])) {
            return;
        }

        return $pluginList[$page - 1];
    }

    protected function updateThemeConfig()
    {
        $vipComponent = [
            'title' => '会员',
            'count' => 4,
            'vipOrder' => 'DESC',
            'vipList' => 'show',
            'background' => '',
            'code' => 'vip',
            'defaultTitle' => '会员',
            'subTitle' => '购买会员，享受更多会员权益',
            'defaultSubTitle' => '购买会员，享受更多会员权益',
            'id' => 'vip'
        ];
        $oldConfig = $this->getThemeService()->getThemeConfigByName('简墨');
        if ($oldConfig){
            if (!isset($oldConfig['confirmConfig']['blocks']['left']['vip'])){
                $oldConfig['confirmConfig']['blocks']['left']['vip'] = $vipComponent;
            }
            if (!isset($oldConfig['config']['blocks']['left']['vip'])){
                $oldConfig['config']['blocks']['left']['vip'] = $vipComponent;
            }
            $this->getThemeService()->editThemeConfig('简墨', ['config' => $oldConfig['config'], 'confirmConfig' => $oldConfig['confirmConfig']]);
        }

        return 1;
    }

    // 执行时长问题，vip插件插入用户权益移至主程序升级脚本运行
    protected function updateUserVipRight($page)
    {
        $pluginApp = $this->getAppService()->getAppByCode('vip');
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装vip');

            return 1;
        }

        if ($page == 1) {
            $this->getConnection()->exec("DELETE FROM vip_user_right;");
        }

        $levels = $this->getConnection()->fetchAll('SELECT * from vip_level order by seq ASC, createdTime DESC;');
        if (empty($levels[$page - 1])) {
            return 1;
        }

        $level = $levels[$page - 1];
        $preLevels = $this->getConnection()->fetchAll('SELECT * from vip_level where seq < ?', [$level['seq']]);
        $levelIds = empty($preLevels) ? [$level['id']] : array_merge([$level['id']], array_column($preLevels, 'id'));
        $ids = implode(',', $levelIds);
        $this->getConnection()->exec("INSERT INTO vip_user_right
        (
            userId,
            vipLevelId,
            supplierCode,
            uniqueCode,
            title,
            createdTime
        )
        SELECT
            v.userId,
            v.levelId,
            vr.supplierCode,
            vr.uniqueCode,
            vr.title,
            unix_timestamp(now())
        FROM vip_right vr , vip v where vr.vipLevelId in ({$ids}) and v.levelId = {$level['id']};");

        $this->logger('info', "更新vip_user_right，当前会员{$level['id']}" . json_encode($ids));

        return $page + 1;
    }

    /**
     * @return \Biz\Theme\Service\ThemeService
     */
    protected function getThemeService()
    {
        return $this->createService('Theme:ThemeService');
    }

    /**
     * @return \Biz\Role\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
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

    protected function getTableCount($table)
    {
        $sql = "select count(*) from `{$table}`;";

        return $this->getConnection()->fetchColumn($sql) ?: 0;
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
