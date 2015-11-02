<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;

class InitCommand extends BaseCommand
{

	protected function configure()
	{
		$this->setName ( 'topxia:init' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>开始初始化系统</info>');

        $this->installAssets($output);
		$this->initServiceKernel();

		$user = $this->initAdminUser($output);

		$this->initSiteSetting($output);
		$this->initConsultSetting($output);
		$this->initRegisterSetting($user, $output);
		$this->initMailerSetting($output);
		$this->initPaymentSetting($output);
		$this->initStorageSetting($output);

		$this->initCategory($output);
		$this->initTag($output);
		$this->initRefundSetting($output);
		$this->initThemes($output);
		$this->initFile($output);
        $this->initDefaultSetting($output);
        $this->initInstallLock($output);
        $this->initBlock($output);
        $this->initCrontabJob($output);

		$output->writeln('<info>初始化系统完毕</info>');
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
            'maxRefundDays' => 10,
            'applyNotification' => '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。',
            'successNotification' => '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。',
            'failedNotification' => '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。',
        );
        $setting = $this->getSettingService()->set('refund', $setting);
        $output->writeln(' ...<info>成功</info>');

	}

	private function initSiteSetting($output)
	{
		$output->write('  初始化站点设置');


        $default = array(
            'name'=>'EDUSOHO测试站',
            'slogan'=>'强大的在线教育解决方案',
            'url'=>'http://demo.edusoho.com',
            'logo'=>'',
            'seo_keywords'=>'edusoho, 在线教育软件, 在线在线教育解决方案',
            'seo_description'=>'edusoho是强大的在线教育开源软件',
            'master_email'=>'test@edusoho.com',
            'icp'=>' 浙ICP备13006852号-1',
            'analytics'=>'',
            'status'=>'open',
            'closed_note'=>''
        );

        $site = $this->getSettingService()->set('site', $default);


	}

	private function initConsultSetting($output)
	{
		$output->write('  初始化客服设置');


         $default = array(
            'enabled' => 0,
            'worktime' => '9:00 - 17:00',
            'qq' => array(
                array('name' => '','number' => ''),
                ),
            'qqgroup' => array(
                array('name' => '','number' => ''),
                ),
            'phone' => array(
                array('name' => '','number' => ''),
                ),
            'webchatURI' => '',
            'email' => '',
            'color' => 'default',
            );
         
        $site = $this->getSettingService()->set('contact', $default);


	}

	private function initAdminUser($output)
	{
		$fields = array(
			'email' => 'test@edusoho.com',
			'nickname' => '测试管理员',
			'password' => 'kaifazhe',
			'roles' => array(),
			'createdIp' => '127.0.0.1',
		);
		$output->write("  创建管理员帐号:{$fields['email']}, 密码：{$fields['password']}   ");

        $user = $this->getUserService()->getUserByEmail('test@edusoho.com');

		if (!$user) {
			$user = $this->getUserService()->register($fields);
		}

        $currentUser = new CurrentUser();
        $user['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($user);
        $token = new UsernamePasswordToken($currentUser, null, 'main', $currentUser->getRoles());
        $this->getContainer()->get('security.context')->setToken($token);

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
            'register_mode'=>'email',
            'email_activation_title' => '请激活您的{{sitename}}帐号',
            'email_activation_body' => trim($emailBody),
            'welcome_enabled' => 'opened',
            'welcome_sender' => $user['nickname'],
            'welcome_methods' => array(),
            'welcome_title' => '欢迎加入{{sitename}}',
            'welcome_body' => '您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。',
        );

        $auth = $this->getSettingService()->set('auth', $default);

		$output->writeln(' ...<info>成功</info>');
	}

	private function initMailerSetting($output)
	{
		$output->write('  初始化邮件服务器设置');

        $default = array(
            'enabled'=>1,
            'host'=>'smtp.exmail.qq.com',
            'port'=>'25',
            'username'=>'test@edusoho.com',
            'password'=>'est123',
            'from'=>'test@edusoho.com',
            'name'=>'TEST',
        );
        $this->getSettingService()->set('mailer', $default);

        $output->writeln(' ...<info>成功</info>');
	}

	private function initPaymentSetting($output)
	{
		$output->write('  初始化支付设置');

        $default = array(
	        'enabled'=>0,
	        'bank_gateway'=>'none',
	        'alipay_enabled'=>0,
	        'alipay_key'=>'',
	        'alipay_secret' => '',
        );
        $payment = $this->getSettingService()->set('payment', $default);

		$output->writeln(' ...<info>成功</info>');
	}

	private function initDefaultSetting($output)
    {
    	$output->write('  初始化章节的默认设置');
        $settingService = $this->getSettingService();

        $defaultSetting = array();
        $defaultSetting['user_name'] ='学员';
        $defaultSetting['chapter_name'] ='章';
        $defaultSetting['part_name'] ='节';

        $default = $settingService->get('default', array());
        $defaultSetting = array_merge($default, $defaultSetting);

        $settingService->set('default', $defaultSetting);
        $output->writeln(' ...<info>成功</info>');
    }

	private function initStorageSetting($output)
	{
		$output->write('  初始化云服务器设置');

        $storageSetting = $this->getSettingService()->get('storage', array());

        $default = array(
            'upload_mode'=>'local',
            'cloud_api_server' => 'http://api.edusoho.net',
            'cloud_access_key'=>'',
            'cloud_bucket'=>'',
            'cloud_secret_key'=>''
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

		$categories = $this->getCategoryService()->findAllCategories();
		foreach ($categories as $category) {
			$this->getCategoryService()->deleteCategory($category['id']);
		}

		$groups = $this->getCategoryService()->findAllGroups();
		foreach ($groups as $group) {
			$this->getCategoryService()->deleteGroup($group['id']);
		}

		$group = $this->getCategoryService()->getGroupByCode('course');
		if (!$group) {
			$group = $this->getCategoryService()->addGroup(array(
				'name' => '课程分类',
				'code' => 'course',
				'depth' => 2,
			));
		}

		$category = $this->getCategoryService()->getCategoryByCode('default');
		if (!$category) {
			$this->getCategoryService()->createCategory(array(
				'name' => '默认分类',
				'code' => 'default',
				'weight' => 100,
				'groupId' => $group['id'],
				'parentId' => 0,
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
            'name' => '默认文件组',
            'code' => 'default',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '缩略图',
            'code' => 'thumb',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '课程',
            'code' => 'course',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '用户',
            'code' => 'user',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '课程私有文件',
            'code' => 'course_private',
            'public' => 0,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '资讯',
            'code' => 'article',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '全局设置文件',
            'code' => 'system',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '小组',
            'code' => 'group',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '编辑区',
            'code' => 'block',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '班级',
            'code' => 'classroom',
            'public' => 1,
        ));

        $directory = $this->getContainer()->getParameter('topxia.disk.local_directory');
        chmod($directory, 0777);

        $directory = $this->getContainer()->getParameter('topxia.upload.private_directory');
        chmod($directory, 0777);

        $directory = $this->getContainer()->getParameter('topxia.upload.public_directory');
        chmod($directory, 0777);

		$output->writeln(' ...<info>成功</info>');
	}

    public function initThemes($output)
    {
    	$output->write('  初始化主题');
    	
        $this->getSettingService()->set('theme', array('uri' => 'default'));

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

    public function initCrontabJob($output){
        $output->write('  初始化CrontabJob');
        $this->getCrontabService()->createJob(array(
            'name'=>'CancelOrderJob', 
            'cycle'=>'everyhour',
            'jobClass'=>'Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob',
            'jobParams'=>'',
            'nextExcutedTime'=>time(),
            'createdTime'=>time()
        ));

        $this->getCrontabService()->createJob(array(
            'name'=>'DeleteExpiredTokenJob', 
            'cycle'=>'everyhour',
            'jobClass'=>'Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob',
            'jobParams'=>'',
            'nextExcutedTime'=>time(),
            'createdTime'=>time()
        ));

        // $this->getCrontabService()->createJob(array(
        //     'name'=>'DeleteSessionJob', 
        //     'cycle'=>'everyhour',
        //     'jobClass'=>'Topxia\\\\Service\\\\User\\\\Job\\\\DeleteSessionJob',
        //     'jobParams'=>'',
        //     'nextExcutedTime'=>time(),
        //     'createdTime'=>time()
        // ));
        
        $this->getSettingService()->set("crontab_next_executed_time", time());

        $output->writeln(' ...<info>成功</info>');
    }

	private function initServiceKernel()
	{
		$serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());

		$serviceKernel->setConnection($this->getContainer()->get('database_connection'));
		$currentUser = new CurrentUser();
		$currentUser->fromArray(array(
		    'id' => 0,
		    'nickname' => '游客',
		    'currentIp' =>  '127.0.0.1',
		    'roles' => array(),
		));
		$serviceKernel->setCurrentUser($currentUser);
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
}