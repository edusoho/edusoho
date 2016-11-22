<?php
namespace Topxia\WebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Topxia\Common\BlockToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

class InitCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('topxia:init');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始初始化系统</info>');

        $this->installAssets($output);
        $this->initServiceKernel();
        $this->initRoles($output);
        $user = $this->initAdminUser($output);

        $this->initSiteSetting($output);
        $this->initConsultSetting($output);
        $this->initRegisterSetting($user, $output);
        $this->initMailerSetting($output);
        $this->initPaymentSetting($output);
        $this->initStorageSetting($output);
        $this->initMagicSetting($output);
        $this->initCategory($output);
        $this->initTag($output);
        $this->initRefundSetting($output);
        $this->initThemes($output);
        $this->initCoin($output);
        $this->initFile($output);
        $this->initDefaultSetting($output);
        $this->initInstallLock($output);
        $this->initBlock($output);
        $this->initCrontabJob($output);
        $this->initFolders();

        $output->writeln('<info>初始化系统完毕</info>');
    }

    private function initFolders()
    {
        $rootDir = realpath($this->getServiceKernel()->getParameter('kernel.root_dir') . '/..');

        $folders = array(
            $rootDir . '/app/data/udisk',
            $rootDir . '/app/data/private_files',
            $rootDir . '/web/files'
        );

        $filesystem = new Filesystem();

        foreach ($folders as $folder) {
            if (!$filesystem->exists($folder)) {
                $filesystem->mkdir($folder);
            }
        }
    }

    private function installAssets($output)
    {
        $command = new AssetsInstallCommand();
        $command->setContainer($this->getContainer());
        $subInput = new StringInput('--symlink --relative');
        $command->run($subInput, $output);
        $output->writeln('<info>installAssets成功</info>');
    }

    private function initRefundSetting($output)
    {
        $output->write('  初始化退款设置');

        $setting = array(
            'maxRefundDays'       => 10,
            'applyNotification'   => '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。',
            'successNotification' => '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。',
            'failedNotification'  => '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。'
        );
        $setting = $this->getSettingService()->set('refund', $setting);
        $output->writeln(' ...<info>成功</info>');
    }

    private function initSiteSetting($output)
    {
        $output->write('  初始化站点设置');

        $default = array(
            'name'            => 'EDUSOHO测试站',
            'slogan'          => '强大的在线教育解决方案',
            'url'             => 'http://demo.edusoho.com',
            'logo'            => '',
            'seo_keywords'    => 'edusoho, 在线教育软件, 在线在线教育解决方案',
            'seo_description' => 'edusoho是强大的在线教育开源软件',
            'master_email'    => 'test@edusoho.com',
            'icp'             => ' 浙ICP备13006852号-1',
            'analytics'       => '',
            'status'          => 'open',
            'closed_note'     => ''
        );

        $site = $this->getSettingService()->set('site', $default);
    }

    private function initMagicSetting($output)
    {
        $default = array(
            'export_allow_count' => 100000,
            'export_limit'       => 10000
        );

        $site = $this->getSettingService()->set('magic', $default);
    }

    private function initConsultSetting($output)
    {
        $output->write('  初始化客服设置');

        $default = array(
            'enabled'    => 0,
            'worktime'   => '9:00 - 17:00',
            'qq'         => array(
                array('name' => '', 'number' => '')
            ),
            'qqgroup'    => array(
                array('name' => '', 'number' => '')
            ),
            'phone'      => array(
                array('name' => '', 'number' => '')
            ),
            'webchatURI' => '',
            'email'      => '',
            'color'      => 'default'
        );

        $site = $this->getSettingService()->set('contact', $default);
    }

    private function initAdminUser($output)
    {
        $fields = array(
            'email'     => 'test@edusoho.com',
            'nickname'  => '测试管理员',
            'password'  => 'kaifazhe',
            'createdIp' => '127.0.0.1'
        );
        $output->write("  创建管理员帐号:{$fields['email']}, 密码：{$fields['password']}   ");

        $user = $this->getUserService()->getUserByEmail('test@edusoho.com');

        if (!$user) {
            $user = $this->getUserService()->register($fields);
        }

        $currentUser       = new CurrentUser();
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        $currentUser->fromArray($user);
        ServiceKernel::instance()->setCurrentUser($currentUser);
        $token = new UsernamePasswordToken($currentUser, null, 'main', $currentUser->getRoles());
        $this->getContainer()->get('security.token_storage')->setToken($token);

        $this->getUserService()->changeUserRoles($user['id'], array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'));

        $output->writeln(' ...<info>成功</info>');

        return $user;
    }

    private function initRegisterSetting($user, $output)
    {
        $output->write('  初始化注册设置');

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

        $default = array(
            'register_mode'          => 'email',
            'email_activation_title' => '请激活您的{{sitename}}帐号',
            'email_activation_body'  => trim($emailBody),
            'welcome_enabled'        => 'opened',
            'welcome_sender'         => $user['nickname'],
            'welcome_methods'        => array(),
            'welcome_title'          => '欢迎加入{{sitename}}',
            'welcome_body'           => '您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。'
        );

        $auth = $this->getSettingService()->set('auth', $default);

        $output->writeln(' ...<info>成功</info>');
    }

    private function initMailerSetting($output)
    {
        $output->write('  初始化邮件服务器设置');

        $default = array(
            'enabled'  => 1,
            'host'     => 'smtp.exmail.qq.com',
            'port'     => '25',
            'username' => 'test@edusoho.com',
            'password' => 'est123',
            'from'     => 'test@edusoho.com',
            'name'     => 'TEST'
        );
        $this->getSettingService()->set('mailer', $default);

        $output->writeln(' ...<info>成功</info>');
    }

    private function initPaymentSetting($output)
    {
        $output->write('  初始化支付设置');

        $default = array(
            'enabled'        => 0,
            'bank_gateway'   => 'none',
            'alipay_enabled' => 0,
            'alipay_key'     => '',
            'alipay_secret'  => ''
        );
        $payment = $this->getSettingService()->set('payment', $default);

        $output->writeln(' ...<info>成功</info>');
    }

    private function initDefaultSetting($output)
    {
        $output->write('  初始化章节的默认设置');
        $settingService = $this->getSettingService();

        $defaultSetting                 = array();
        $defaultSetting['chapter_name'] = '章';
        $defaultSetting['part_name']    = '节';

        $default        = $settingService->get('default', array());
        $defaultSetting = array_merge($default, $defaultSetting);

        $settingService->set('default', $defaultSetting);

        $setting = array(
            'rules' => array(
                'thread'            => array(
                    'fiveMuniteRule' => array(
                        'interval' => 300,
                        'postNum'  => 100
                    )
                ),
                'threadLoginedUser' => array(
                    'fiveMuniteRule' => array(
                        'interval' => 300,
                        'postNum'  => 50
                    )
                )
            )
        );
        $settingService->set('post_num_rules', $setting);

        $settingService->get('developer', array());
        $developer['cloud_api_failover'] = 1;
        $settingService->set('developer', $developer);

        $output->writeln(' ...<info>成功</info>');
    }

    private function initStorageSetting($output)
    {
        $output->write('  初始化云服务器设置');

        $storageSetting = $this->getSettingService()->get('storage', array());

        $default = array(
            'upload_mode'      => 'local',
            'cloud_api_server' => 'http://api.edusoho.net',
            'cloud_access_key' => '',
            'cloud_secret_key' => ''
        );

        $storageSetting = $this->getSettingService()->set('storage', $default);

        $output->writeln(' ...<info>成功</info>');
    }

    private function initTag($output)
    {
        $output->write('  初始化标签');

        $defaultTag = $this->getTagService()->getTagByName('默认标签');

        if (!$defaultTag) {
            $this->getTagService()->addTag(array('name' => '默认标签'));
        }

        $output->writeln(' ...<info>成功</info>');
    }

    private function initCategory($output)
    {
        $output->write('  初始化分类分组');

        $group = $this->getCategoryService()->getGroupByCode('course');

        if (!$group) {
            $group = $this->getCategoryService()->addGroup(array(
                'name'  => '课程分类',
                'code'  => 'course',
                'depth' => 2
            ));
        }

        $category = $this->getCategoryService()->getCategoryByCode('default');

        if (!$category) {
            $this->getCategoryService()->createCategory(array(
                'name'     => '默认分类',
                'code'     => 'default',
                'weight'   => 100,
                'groupId'  => $group['id'],
                'parentId' => 0
            ));
        }

        $output->writeln(' ...<info>成功</info>');
    }

    private function initFile($output)
    {
        $output->write('  初始化文件分组');

        $groups = $this->getFileService()->getAllFileGroups();

        foreach ($groups as $group) {
            $this->getFileService()->deleteFileGroup($group['id']);
        }

        $this->getFileService()->addFileGroup(array(
            'name'   => '默认文件组',
            'code'   => 'default',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '缩略图',
            'code'   => 'thumb',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '课程',
            'code'   => 'course',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '用户',
            'code'   => 'user',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '课程私有文件',
            'code'   => 'course_private',
            'public' => 0
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '资讯',
            'code'   => 'article',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '临时目录',
            'code'   => 'tmp',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '全局设置文件',
            'code'   => 'system',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '小组',
            'code'   => 'group',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '编辑区',
            'code'   => 'block',
            'public' => 1
        ));

        $this->getFileService()->addFileGroup(array(
            'name'   => '班级',
            'code'   => 'classroom',
            'public' => 1
        ));

        $directory = $this->getContainer()->getParameter('topxia.disk.local_directory');
        @chmod($directory, 0777);

        $directory = $this->getContainer()->getParameter('topxia.upload.private_directory');
        @chmod($directory, 0777);

        $directory = $this->getContainer()->getParameter('topxia.upload.public_directory');
        @chmod($directory, 0777);

        $output->writeln(' ...<info>成功</info>');
    }

    public function initThemes($output)
    {
        $output->write('  初始化主题');

        $this->getSettingService()->set('theme', array('uri' => 'jianmo'));

        $output->writeln(' ...<info>成功</info>');
    }

    public function initCoin($output)
    {
        $output->write('  初始化虚拟币');

        $default = array(
            'cash_model'   => "none",
            'price_type'   => "RMB",
            'cash_rate'    => 1,
            'coin_enabled' => 0
        );

        $this->getSettingService()->set('coin', $default);

        $output->writeln(' ...<info>成功</info>');
    }

    public function initInstallLock($output)
    {
        $output->write('  初始化install.lock');
        touch($this->getContainer()->getParameter('kernel.root_dir') . '/data/install.lock');
        touch($this->getContainer()->getParameter('kernel.root_dir') . '/config/routing_plugins.yml');

        $output->writeln(' ...<info>成功</info>');
    }

    public function initBlock($output)
    {
        $output->write('  初始化编辑区');
        $json = dirname($this->getContainer()->getParameter('kernel.root_dir')) . '/web/themes/block.json';
        BlockToolkit::init($json, $this->getContainer());

        $json = dirname($this->getContainer()->getParameter('kernel.root_dir')) . '/web/themes/default/block.json';
        BlockToolkit::init($json, $this->getContainer());

        $json = dirname($this->getContainer()->getParameter('kernel.root_dir')) . '/web/themes/autumn/block.json';
        BlockToolkit::init($json, $this->getContainer());

        $json = dirname($this->getContainer()->getParameter('kernel.root_dir')) . '/web/themes/jianmo/block.json';
        BlockToolkit::init($json, $this->getContainer());
    }

    public function initCrontabJob($output)
    {
        $output->write('  初始化CrontabJob');

        $this->getCrontabService()->createJob(array(
            'name' => 'CancelOrderJob',
            'cycle' => 'everyhour',
            'jobClass' => 'Topxia\\Service\\Order\\Job\\CancelOrderJob',
            'nextExcutedTime' => time(),
            'createdTime' => time()
        ));

        $this->getCrontabService()->createJob(array(
            'name' => 'DeleteExpiredTokenJob',
            'cycle' => 'everyhour',
            'jobClass' => 'Topxia\\Service\\User\\Job\\DeleteExpiredTokenJob',
            'nextExcutedTime' => time(),
            'createdTime' => time()
        ));

        $this->getCrontabService()->setNextExcutedTime(time());

        $output->writeln(' ...<info>成功</info>');
    }

    protected function initRoles($output)
    {
        $output->write('  初始化角色');
        $this->getRoleService()->refreshRoles();
        $output->writeln(' ...<info>成功</info>');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCrontabService()
    {
        return $this->getServiceKernel()->createService('Crontab.CrontabService');
    }

    protected function getRoleService()
    {
        return $this->getServiceKernel()->createService('Permission:Role.RoleService');
    }
}
