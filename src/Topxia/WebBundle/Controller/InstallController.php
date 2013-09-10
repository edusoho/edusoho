<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class InstallController extends BaseController
{

    public function initSystemAction(Request $request)
    {

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->createSuperManager($formData);
            $this->initSiteSetting($formData['site-name']);
            $this->processParametersYml($formData); 

            $super_manager = $this->getUserService()->searchUsers(array('roles'=>'ROLE_SUPER_ADMIN'), array('createdTime', 'DESC'), 0, 1);
            $super_manager = $super_manager[0];

            $this->initAuthSetting($super_manager['nickname']);
            $this->initMailerSetting();
            $this->initCategorySetting();
            $this->initFileSetting();

            return $this->redirect($this->generateUrl('install_welcome')); 
        }
        
        return $this->render("TopxiaWebBundle:Install:init-system.html.twig"); 
    }

    public function welcomeAction(Request $request)
    {
        return $this->render("TopxiaWebBundle:Install:welcome.html.twig"); 
    }

    private function initMailerSetting()
    {
        $defaultMailer = array(
            'enabled'=>1,
            'host'=>'smtp.exmail.qq.com',
            'port'=>'25',
            'username'=>'test@edusoho.com',
            'password'=>'est123',
            'from'=>'test@edusoho.com',
            'name'=>'TEST'
        );
        $this->getSettingService()->set('mailer', $defaultMailer);

        $defaultTag = $this->getTagService()->getTagByName('默认标签');
        if (!$defaultTag) {
            $this->getTagService()->addTag(array('name' => '默认标签'));
        }
    }

    private function initCategorySetting()
    {
        $categories = $this->getCategoryService()->findAllCategories();
        foreach ($categories as $category) {
            $this->getCategoryService()->deleteCategory($category['id']);
        }

        $categoryGroups = $this->getCategoryService()->findAllGroups();
        foreach ($categoryGroups as $group) {
            $this->getCategoryService()->deleteGroup($group['id']);
        }

        $categoryGroup = $this->getCategoryService()->getGroupByCode('course');
        if (!$categoryGroup) {
            $categoryGroup = $this->getCategoryService()->addGroup(array(
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
                'groupId' => $categoryGroup['id'],
                'parentId' => 0,
            ));
        }

    }

    private function initFileSetting()
    {
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
    }

    private function initAuthSetting($super_manager_nickname)
    {
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

        $defaultRegister = array(
            'register_mode'=>'opened',
            'email_activation_title' => '请激活您的{{sitename}}帐号',
            'email_activation_body' => trim($emailBody),
            'welcome_enabled' => 'opened',
            'welcome_sender' => $super_manager_nickname,
            'welcome_methods' => array(),
            'welcome_title' => '欢迎加入{{sitename}}',
            'welcome_body' => '您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。'
        );

        $auth = $this->getSettingService()->set('auth', $defaultRegister);
    }

    private function createSuperManager($formData)
    {
        $registration = array();
        $registration['email'] = $formData['super_manager_email'];
        $registration['password'] = $registration['confirmPassword'] = $formData['super_manager_pd'] ;
        $registration['nickname'] = $formData['super_manager'] ;
        $this->getSettingService()->get('auth', array());
        $user = $this->getUserService()->register($registration);
        $this->authenticateUser($user);
        $this->get('session')->set('registed_email', $user['email']);
        $this->getUserService()->changeUserRoles($user['id'], array('ROLE_TEACHER', 'ROLE_SUPER_ADMIN'));
    }

    private function initSiteSetting($sitename)
    {
        $siteFields = array(
            'name'=>$sitename,
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
            'homepage_template'=>'less');
        $this->getSettingService()->set('site', $siteFields);
    }

    private function processParametersYml($formData)
    {
        $dumper = new Dumper();
        $parser = new Parser();
        $parameters = $parser->parse(file_get_contents('/var/www/edusoho/app/config/parameters.yml'));
        $parameters['parameters']['mailer_user'] = $formData['super_manager'];
        $parameters['parameters']['mailer_password'] = $formData['super_manager_pd'];
        $yaml = $dumper->dump($parameters, 2);
        file_put_contents('/var/www/edusoho/app/config/parameters.yml', $yaml);
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

