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
            'roleAddDataV2',
            'updateOldAdminDisableRoles',
            'upgradeRoleDataV2',
            'updateAdminV2Setting',
            'addTableQuickEntrance',
            'mobileUpgrade',
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

    public function updateOldAdminDisableRoles()
    {
        $disableRoles = array(
            'admin_setting_operation' => array(
                'admin_article_setting',
                'admin_group_set',
                'admin_invite_set',
                'admin_message_setting',
            ),
            'admin_setting_user' => array(
                'admin_user_auth',
                'admin_setting_login_bind',
                'admin_setting_user_center',
                'admin_setting_user_fields',
                'admin_setting_avatar',
                'admin_setting_user_message',
            ),
            'admin_setting' => array(
                'admin_setting_message',
                'admin_setting_theme',
                'admin_setting_mailer',
                'admin_top_navigation',
                'admin_foot_navigation',
                'admin_friendlyLink_navigation',
                'admin_setting_consult_setting',
                'admin_setting_es_bar',
                'admin_setting_share',
                'admin_setting_security',
            ),
            'admin_operation_wechat_notification' => array(
                'admin_operation_wechat_fans_list',
                'admin_operation_wechat_notification_record',
                'admin_operation_wechat_notification_manage',
            ),
        );

        $roles = $this->getRoleService()->searchRoles(array('excludeCodes' => array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN','ROLE_SCHOOL_ADMIN')), array(), 0, PHP_INT_MAX);

        foreach ($roles as &$role) {
            foreach ($disableRoles as $key => $disableRole) {
                if (in_array($key, $role['data'])) {
                    $role['data'] = array_merge($role['data'], $disableRole);
                }
            }
            $role['data'] = array_unique($role['data']);
            $role['data'] = array_filter($role['data']);
            $role['data'] = array_values($role['data']);
            $this->getRoleService()->updateRole($role['id'], $role);
        }

        return 1;
    }

    public function roleAddDataV2()
    {
        if (!$this->isFieldExist('role', 'data_v2')) {
            $this->getConnection()->exec("
                ALTER TABLE `role` ADD `data_v2` text COMMENT 'admin_v2权限配置' AFTER `data`;
            ");
        }

        return 1;
    }

    public function upgradeRoleDataV2()
    {
        $this->getRoleService()->refreshRoles();
        $this->getRoleService()->upgradeRoleDataV2();

        return 1;
    }

    public function updateAdminV2Setting()
    {
        $setting = $this->getSettingService()->get('backstage', array('is_v2' => 0));

        $setting['allow_show_switch_btn'] = 1;
        $this->getSettingService()->set('backstage', $setting);

        return 1;
    }

    public function addTableQuickEntrance()
    {
        if (!$this->isTableExist('quick_entrance')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `quick_entrance` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) NOT NULL COMMENT '用户ID',
                  `data` text COMMENT '常用功能',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='常用功能';
            ");
        }

        return 1;
    }

    public function mobileUpgrade()
    {
        $appDiscoveryVersion = $this->getH5SettingService()->getAppDiscoveryVersion();

        if (0 == $appDiscoveryVersion) {
            try {
                $bannersSetting = $this->getAppBannersSetting();
                $channelSettings = $this->getAppChannelSettings();
                $appSettings = array_merge($bannersSetting, $channelSettings);

                $this->getSettingService()->set('app_discovery', array('version' => 1));
                $this->getSettingService()->set('apps_published_discovery', $appSettings);

                return 1;
            } catch (\Exception $e) {
                $this->getConnection()->rollback();
                $this->logger('error', $e->getTraceAsString());
                throw $e;
            }
        }

        return 1;
    }

    protected function getAppChannelSettings()
    {
        $index = 1;

        $settings = array();

        $discoveryColumns = $this->getDiscoveryColumnService()->getDisplayData();

        $sortTypes = array(
            'hot' => '-studentNum',
            'new' => '-createdTime',
            'recommend' => 'recommendedSeq',
        );

        foreach ($discoveryColumns as $discoveryColumn) {
            $setting = array(
                'type' => '',
                'moduleType' => '',
                'data' => array(
                    'title' => '',
                    'sourceType' => 'condition',
                    'categoryId' => '',
                    'sort' => '',
                    'lastDays' => 0,
                    'limit' => '',
                    'items' => array(),
                ),
            );

            if (0 < intval($discoveryColumn['categoryId'])) {
                $setting['data']['categoryIdArray'] = \AppBundle\Common\ArrayToolkit::column(
                    $this->getCategoryService()->findCategoryBreadcrumbs($discoveryColumn['categoryId']),
                    'id'
                );
            }

            switch ($discoveryColumn['type']) {
                case 'classroom':
                    $setting['type'] = 'classroom_list';
                    $setting['moduleType'] = 'classroom_list-'.$index;
                    $setting['data']['categoryId'] = $discoveryColumn['categoryId'];
                    $setting['data']['sort'] = empty($discoveryColumn['orderType']) ? '' : $sortTypes[$discoveryColumn['orderType']];
                    $setting['data']['limit'] = $discoveryColumn['showCount'];
                    $setting['data']['title'] = $discoveryColumn['title'];
                    break;

                case 'live':
                    $setting['type'] = 'course_list';
                    $setting['moduleType'] = 'course_list-'.$index;
                    $setting['data']['sourceType'] = 'custom';
                    $setting['data']['categoryId'] = $discoveryColumn['categoryId'];
                    $setting['data']['sort'] = '-createdTime';
                    $setting['data']['limit'] = $discoveryColumn['showCount'];
                    $setting['data']['title'] = $discoveryColumn['title'];

                    $conditions = array(
                        'status' => 'published',
                        'parentId' => 0,
                        'type' => 'live',
                        'excludeTypes' => array('reservation'),
                        'courseSetStatus' => 'published',
                    );
                    if (isset($setting['data']['categoryIdArray'])) {
                        $conditions['categoryIds'] = $setting['data']['categoryIdArray'];
                    }
                    $setting['data']['items'] = $this->getCourseService()->searchCourses($conditions, '', 0, $discoveryColumn['showCount']);

                    break;

                case 'course':
                    $setting['type'] = 'course_list';
                    $setting['moduleType'] = 'course_list-'.$index;
                    $setting['data']['categoryId'] = $discoveryColumn['categoryId'];
                    $setting['data']['sort'] = empty($discoveryColumn['orderType']) ? '' : $sortTypes[$discoveryColumn['orderType']];
                    $setting['data']['limit'] = $discoveryColumn['showCount'];
                    $setting['data']['title'] = $discoveryColumn['title'];
                    $setting['data']['source'] = array(
                        'courseType' => 'all',
                        'category' => $discoveryColumn['categoryId'],
                        'sort' => empty($discoveryColumn['orderType']) ? '' : $sortTypes[$discoveryColumn['orderType']],
                    );
                    break;

                default:
                    break;
            }

            $settings[$setting['moduleType']] = $setting;

            ++$index;
        }

        return $settings;
    }

    protected function getAppBannersSetting()
    {
        global $kernel;
        $banners = json_decode(
            file_get_contents($kernel->getContainer()->get('request')->getSchemeAndHttpHost().'/mapi_v2/School/getSchoolBanner'),
            true
        );

        $setting = array();
        if (!empty($banners)) {
            $setting['slide-1'] = array(
                'type' => 'slide_show',
                'moduleType' => 'slide-1',
                'data' => array(),
            );

            foreach ($banners as $banner) {
                switch ($banner['action']) {
                    case 'webview':
                        $link = array(
                            'type' => 'url',
                            'target' => null,
                            'url' => $banner['params'],
                        );
                        break;
                    case 'none':
                        $link = array(
                            'type' => 'none',
                            'target' => null,
                            'url' => '',
                        );
                        break;
                    case 'course':
                        $course = $this->getCourseService()->getCourse($banner['params']);
                        if (!empty($course)) {
                            $target = array(
                                'id' => $course['id'],
                                'courseSetId' => $course['courseSetId'],
                                'title' => $course['title'],
                                'displayedTitle' => \Biz\Course\Util\CourseTitleUtils::getDisplayedTitle($course),
                            );
                        } else {
                            $target = null;
                        }
                        $link = array(
                            'type' => 'course',
                            'target' => $target,
                            'url' => '',
                        );
                        break;
                    default:
                        $link = array(
                            'type' => '',
                            'target' => null,
                            'url' => '',
                        );
                        break;
                }

                $setting['slide-1']['data'][] = array(
                    'title' => '',
                    'image' => array(
                        'id' => 0,
                        'size' => 0,
                        'createdTime' => date('c'),
                        'uri' => $banner['url'],
                    ),
                    'link' => $link,
                );
            }
        }

        return $setting;
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
