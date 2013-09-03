<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends BaseCommand
{

	protected function configure()
	{
		$this->setName ( 'topxia:init' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>开始初始化系统</info>');

		$user = $this->initAdminUser($output);

		$this->initSiteSetting($output);
		$this->initRegisterSetting($user, $output);
		$this->initMailerSetting($output);
		$this->initPaymentSetting($output);
		$this->initCloudSetting($output);

		$this->initCategory($output);
		$this->initTag($output);
		$this->initFile($output);

		$output->writeln('<info>初始化系统完毕</info>');
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
            'closed_note'=>'',
            'homepage_template'=>'less'
        );

        $site = $this->getSettingService()->set('site', $default);

        $output->writeln(' ...<info>成功</info>');


	}

	private function initAdminUser($output)
	{
		$fields = array(
			'email' => 'test@edusoho.com',
			'nickname' => '测试管理员',
			'password' => 'testtest',
		);
		$output->write("  创建管理员帐号:{$fields['email']}, 密码：{$fields['password']}   ");

		$user = $this->getUserService()->getUserByEmail('test@edusoho.com');
		if (!$user) {
			$user = $this->getUserService()->register($fields);
		}

		$this->getUserService()->changeUserRoles($user['id'], array('ROLE_SUPER_ADMIN', 'ROLE_TEACHER'));

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
            'register_mode'=>'opened',
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
            'enabled'=>1,
            'bank_gateway'=>'none',
            'alipay_enabled'=>1,
            'alipay_key'=>'2088801030402123',
            'alipay_secret' => '',
        );
        $payment = $this->getSettingService()->set('payment', $default);

		$output->writeln(' ...<info>成功</info>');
	}

	private function initCloudSetting($output)
	{
		$output->write('  初始化云服务器设置');

        $videoSetting = $this->getSettingService()->get('video', array());

        $default = array(
            'upload_mode'=>'cloud',
            'cloud_access_key'=>'mY2qzVfN1YR45jdDlk6zyu7EGk3OtiUJdtNX_O0f',
            'cloud_bucket'=>'edusoho-dev',
            'cloud_secret_key'=>'o9kPc_isTvHPBG5vtvwkWVAwG-HbUFKQ0nz3yJyQ'
        );

        $videoSetting = $this->getSettingService()->set('video', $default);
		
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

		$group = $this->getCategoryService()->addGroup(array(
			'name' => '课程分类',
			'code' => 'course',
			'depth' => 2,
		));

		$this->getCategoryService()->createCategory(array(
			'name' => '默认分类',
			'code' => 'default',
			'weight' => 100,
			'groupId' => $group['id'],
			'parentId' => 0,
		));

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

        $directory = $this->getContainer()->getParameter('topxia.disk.local_directory');
        chmod($directory, 0777);

        $directory = $this->getContainer()->getParameter('topxia.upload.private_directory');
        chmod($directory, 0777);

        $directory = $this->getContainer()->getParameter('topxia.upload.public_directory');
        chmod($directory, 0777);

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

}