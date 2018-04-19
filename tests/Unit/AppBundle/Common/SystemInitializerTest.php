<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Symfony\Component\Console\Output\ConsoleOutput;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\Filesystem\Filesystem;

class SystemInitializerTest extends BaseTestCase
{
    public function testInit()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        $initializer->init();
    }

    public function testInitPages()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initPages', array());
        $result = $this->getContentService()->searchContents(array(), array('createdTime' => 'DESC'), 0, \PHP_INT_MAX);

        $this->assertArrayEquals(array(
            'title' => '关于我们',
            'type' => 'page',
            'alias' => 'aboutus',
            'body' => '',
            'template' => 'default',
            'status' => 'published',
        ), ArrayToolkit::parts($result[0], array('title', 'type', 'alias', 'body', 'template', 'status')));

        $this->assertArrayEquals(array(
            'title' => '常见问题',
            'type' => 'page',
            'alias' => 'questions',
            'body' => '',
            'template' => 'default',
            'status' => 'published',
        ), ArrayToolkit::parts($result[1], array('title', 'type', 'alias', 'body', 'template', 'status')));
    }

    public function testInitCoin()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initCoin', array());
        $result = $this->getSettingService()->get('coin');
        $default = array(
            'cash_model' => 'none',
            'cash_rate' => 1,
            'coin_enabled' => 0,
        );

        $this->assertArrayEquals($default, $result);
    }

    public function testInitNavigations()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initNavigations', array());
        $result = $this->getNavigationService()->searchNavigations(array(), array(), 0, \PHP_INT_MAX);

        $this->assertArrayEquals(array(
            'name' => '师资力量',
            'url' => 'teacher',
            'sequence' => 1,
            'isNewWin' => 0,
            'isOpen' => 1,
            'type' => 'top',
        ), ArrayToolkit::parts($result[0], array('name', 'url', 'sequence', 'isNewWin', 'isOpen', 'type')));

        $this->assertArrayEquals(array(
            'name' => '常见问题',
            'url' => 'page/questions',
            'sequence' => 2,
            'isNewWin' => 0,
            'isOpen' => 1,
            'type' => 'top',
        ), ArrayToolkit::parts($result[1], array('name', 'url', 'sequence', 'isNewWin', 'isOpen', 'type')));

        $this->assertArrayEquals(array(
            'name' => '关于我们',
            'url' => 'page/aboutus',
            'sequence' => 3,
            'isNewWin' => 0,
            'isOpen' => 1,
            'type' => 'top',
        ), ArrayToolkit::parts($result[2], array('name', 'url', 'sequence', 'isNewWin', 'isOpen', 'type')));
    }

    public function testInitThemes()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initThemes', array());

        $result = $this->getSettingService()->get('theme');
        $default = array('uri' => 'jianmo');
        $this->assertArrayEquals($default, $result);
    }

    public function testInitBlocks()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initBlocks', array());

        $result = $this->getBlockService()->searchBlockTemplates(array(), array(), 0, \PHP_INT_MAX);
        $result = ArrayToolkit::column($result, 'code');

        $this->assertArrayEquals(
            array(
                'live_top_banner',
                'default:home_top_banner',
                'autumn:home_top_banner',
                'jianmo:home_top_banner',
                'jianmo:middle_banner',
                'jianmo:advertisement_banner',
                'jianmo:bottom_info',
            ),
            $result
        );

        $result = $this->getBlockDao()->search(array(), array(), 0, \PHP_INT_MAX);
        $this->assertTrue(empty($result));
    }

    public function testInitJob()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initJob', array());

        $result = $this->getSchedulerService()->searchJobs(array(), array(), 0, \PHP_INT_MAX);

        $this->assertEquals(20, count($result));

        $this->assertArrayEquals(array(
            'Order_FinishSuccessOrdersJob',
            'Order_CloseOrdersJob',
            'DeleteExpiredTokenJob',
            'SessionGcJob',
            'OnlineGcJob',
            'Scheduler_MarkExecutingTimeoutJob',
            'RefreshLearningProgressJob',
            'UpdateInviteRecordOrderInfoJob',
            'Xapi_PushStatementsJob',
            'Xapi_AddActivityWatchToStatementJob',
            'Xapi_ArchiveStatementJob',
            'Xapi_ConvertStatementsJob',
            'SyncUserTotalLearnStatisticsJob',
            'SyncUserLearnDailyPastLearnStatisticsJob',
            'DeleteUserLearnDailyPastLearnStatisticsJob',
            'SyncUserLearnDailyLearnStatisticsJob',
            'StorageDailyLearnStatisticsJob',
            'DistributorSyncJob',
            'DeleteFiredLogJob',
            'updateCourseSetHotSeq',
            ), ArrayToolkit::column($result, 'name'));
    }

    public function testInitSystemUsers()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initSystemUsers', array());
        $result = $this->getUserService()->searchUsers(array(), array(), 0, \PHP_INT_MAX);
        $this->assertEquals(1, count($result));
        $this->assertArrayEquals(array(
            'email' => 'admin@admin.com',
            'nickname' => 'admin',
            'type' => 'default',
            'emailVerified' => '0',
            'roles' => array(
                0 => 'ROLE_USER',
                1 => 'ROLE_ADMIN',
                2 => 'ROLE_SUPER_ADMIN',
                3 => 'ROLE_TEACHER',
            ),
            'orgId' => '1',
            'orgCode' => '1.',
        ), ArrayToolkit::parts($result[0], array('nickname', 'emailVerified', 'orgId', 'orgCode', 'email', 'password', 'type', 'roles')));
    }

    public function testInitFolders()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, 'initFolders', array());

        $folders = array(
            $this->biz['kernel.root_dir'].'/data/udisk',
            $this->biz['kernel.root_dir'].'/data/private_files',
            $this->biz['kernel.root_dir'].'/../web/files',
        );

        $filesystem = new Filesystem();

        foreach ($folders as $folder) {
            $this->assertTrue($filesystem->exists($folder));
        }
    }

    public function testInitRole()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initRole', array());

        $reuslt = $this->getRoleService()->searchRoles(array(), array(), 0, \PHP_INT_MAX);
        $this->assertArrayEquals(array(
        0 => 'ROLE_USER',
        1 => 'ROLE_TEACHER',
        2 => 'ROLE_ADMIN',
        3 => 'ROLE_SUPER_ADMIN',
        ), ArrayToolkit::column($reuslt, 'code'));
    }

    public function testinitLockFile()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        $initializer->initLockFile();
        $filesystem = new Filesystem();
        $files = array(
            $this->biz['kernel.root_dir'].'/data/install.lock',
            $this->biz['kernel.root_dir'].'/config/routing_plugins.yml',
        );

        foreach ($files as $file) {
            $this->assertTrue($filesystem->exists($file));
        }
    }

    public function testInitOrg()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initOrg', array());
        $result = $this->getOrgService()->searchOrgs(array(), array(), 0, \PHP_INT_MAX);

        $this->assertArrayEquals(array(
            'name' => '全站',
            'parentId' => '0',
            'childrenNum' => '0',
            'depth' => '1',
            'seq' => '0',
            'description' => null,
            'code' => 'FullSite',
            'orgCode' => '1.',
            'createdUserId' => '1',
        ), ArrayToolkit::parts($result[0], array('name', 'parentId', 'childrenNum', 'depth', 'description', 'seq', 'code', 'orgCode', 'createdUserId')));
    }

    public function testInitFile()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        ReflectionUtils::invokeMethod($initializer, '_initFile', array());

        $result = $this->getFileService()->getAllFileGroups();

        $this->assertArrayEquals(array(
            array(
                'name' => '默认文件组',
                'code' => 'default',
                'public' => 1,
            ),
            array(
                'name' => '缩略图',
                'code' => 'thumb',
                'public' => 1,
            ),
            array(
                'name' => '课程',
                'code' => 'course',
                'public' => 1,
            ),
            array(
                'name' => '用户',
                'code' => 'user',
                'public' => 1,
            ),
            array(
                'name' => '课程私有文件',
                'code' => 'course_private',
                'public' => 0,
            ),
            array(
                'name' => '资讯',
                'code' => 'article',
                'public' => 1,
            ),
            array(
                'name' => '临时目录',
                'code' => 'tmp',
                'public' => 1,
            ),
            array(
                'name' => '全局设置文件',
                'code' => 'system',
                'public' => 1,
            ),
            array(
                'name' => '小组',
                'code' => 'group',
                'public' => 1,
            ),
            array(
                'name' => '编辑区',
                'code' => 'block',
                'public' => 1,
            ),
            array(
                'name' => '班级',
                'code' => 'classroom',
                'public' => 1,
            ),
        ), $result);
    }

    public function testInitCategory()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initCategory', array());
        $courseGroup = $this->getCategoryService()->getGroupByCode('course');
        $courseCategory = $this->getCategoryService()->getCategoryByCode('default');
        $classroomGroup = $this->getCategoryService()->getGroupByCode('classroom');
        $classroomCategory = $this->getCategoryService()->getCategoryByCode('classroomdefault');

        $this->assertNotTrue(empty($courseGroup));
        $this->assertNotTrue(empty($courseCategory));
        $this->assertNotTrue(empty($classroomGroup));
        $this->assertNotTrue(empty($classroomCategory));
    }

    public function testInitTag()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initTag', array());
        $result = $this->getTagService()->getTagByName('默认标签');

        $this->assertNotTrue(empty($result));
    }

    public function testInitRegisterSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, 'initRegisterSetting', array(array('nickname' => 'test')));
        $result = $this->getSettingService()->get('auth');

        $emailBody = <<<'EOD'
Hi, {{nickname}}

欢迎加入{{sitename}}!

请点击下面的链接完成注册：

{{verifyurl}}

如果以上链接无法点击，请将上面的地址复制到你的浏览器(如IE)的地址栏中打开，该链接地址24小时内打开有效。

感谢对{{sitename}}的支持！

{{sitename}} {{siteurl}}

(这是一封自动产生的email，请勿回复。)
EOD;
        $this->assertArrayEquals(array(
            'register_mode' => 'email',
            'email_activation_title' => '请激活您的{{sitename}}帐号',
            'email_activation_body' => trim($emailBody),
            'welcome_enabled' => 'opened',
            'welcome_sender' => 'test',
            'welcome_methods' => array(),
            'welcome_title' => '欢迎加入{{sitename}}',
            'welcome_body' => '您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。',
        ), $result);
    }

    public function testInitStorageSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initStorageSetting', array());
        $result = $this->getSettingService()->get('storage');
        $this->assertArrayEquals(array(
            'upload_mode' => 'local',
            'cloud_api_server' => 'http://api.edusoho.net',
            'cloud_access_key' => '',
            'cloud_secret_key' => '',
        ), $result);
    }

    public function testInitDefaultSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initDefaultSetting', array());
        $result = $this->getSettingService()->get('default');
        $this->assertArrayEquals(array(
            'chapter_name' => '章',
            'user_name' => '学员',
            'part_name' => '节',
        ), $result);

        $result = $this->getSettingService()->get('post_num_rules');
        $this->assertArrayEquals(array(
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
        ), $result);

        $result = $this->getSettingService()->get('developer');
        $this->assertArrayEquals(array(), $result);

        $result = $this->getSettingService()->get('developer');
        $this->assertArrayEquals(array(
            'cloud_api_failover' => 1,
        ), $result);
    }

    public function testInitPaymentSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initPaymentSetting', array());
        $result = $this->getSettingService()->get('payment');
        $this->assertArrayEquals(array(
            'enabled' => 0,
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_accessKey' => '',
            'alipay_secretKey' => '',
        ), $result);
    }

    public function testInitSiteSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initSiteSetting', array());
        $result = $this->getSettingService()->get('site');
        $this->assertArrayEquals(array(
            'name' => 'EDUSOHO测试站',
            'slogan' => '强大的在线教育解决方案',
            'url' => 'http://demo.edusoho.com',
            'logo' => '',
            'seo_keywords' => 'edusoho, 在线教育软件, 在线在线教育解决方案',
            'seo_description' => 'edusoho是强大的在线教育开源软件',
            'master_email' => 'test@edusoho.com',
            'icp' => ' 浙ICP备13006852号-1',
            'analytics' => '',
            'status' => 'open',
            'closed_note' => '',
        ), $result);
    }

    public function testInitRefundSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initRefundSetting', array());
        $result = $this->getSettingService()->get('refund');
        $this->assertArrayEquals(array(
            'maxRefundDays' => 10,
            'applyNotification' => '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。',
            'successNotification' => '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。',
            'failedNotification' => '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。',
        ), $result);
    }

    public function testInitConsultSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initConsultSetting', array());
        $result = $this->getSettingService()->get('contact');
        $this->assertArrayEquals(array(
            'enabled' => 0,
            'worktime' => '9:00 - 17:00',
            'qq' => array(
                array('name' => '', 'number' => ''),
            ),
            'qqgroup' => array(
                array('name' => '', 'number' => ''),
            ),
            'phone' => array(
                array('name' => '', 'number' => ''),
            ),
            'webchatURI' => '',
            'email' => '',
            'color' => 'default',
        ), $result);
    }

    public function testInitMagicSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initMagicSetting', array());
        $result = $this->getSettingService()->get('magic');
        $this->assertArrayEquals(array(
            'export_allow_count' => 100000,
            'export_limit' => 10000,
            'enable_org' => 0,
        ), $result);
    }

    public function testInitMailerSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initMailerSetting', array());
        $result = $this->getSettingService()->get('mailer');
        $this->assertArrayEquals(array(
            'enabled' => 0,
            'host' => 'smtp.exmail.qq.com',
            'port' => '25',
            'username' => 'user@example.com',
            'password' => '',
            'from' => 'user@example.com',
            'name' => '',
        ), $result);
    }

    public function testInitAdminUser()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        $fields = array(
            'email' => 'test@edusoho.com',
            'password' => 'test',
            'nickname' => 'testnickname',
        );
        $result = $initializer->initAdminUser($fields);

        $this->assertEquals('test@edusoho.com', $result['email']);
        $this->assertEquals('testnickname', $result['nickname']);
        $this->assertArrayEquals(array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_SUPER_ADMIN'), $result['roles']);
    }

    public function testInitCustom()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        $initializer->_initCustom();
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getCategoryDao()
    {
        return $this->createDao('Article:CategoryDao');
    }

    protected function getBlockDao()
    {
        return $this->createDao('Content:BlockDao');
    }

    private function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    private function getContentService()
    {
        return $this->createService('Content:ContentService');
    }

    protected function getNavigationService()
    {
        return $this->createService('Content:NavigationService');
    }

    protected function getBlockService()
    {
        return $this->createService('Content:BlockService');
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }
}
