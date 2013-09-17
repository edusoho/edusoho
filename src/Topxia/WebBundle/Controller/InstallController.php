<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InstallController extends BaseController
{

    public function initSystemAction(Request $request)
    {

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->createSuperManager($formData);
            $this->initSiteSetting($formData['site-name']);

            $super_manager = $this->getUserService()->searchUsers(array('roles'=>'ROLE_SUPER_ADMIN'), array('createdTime', 'DESC'), 0, 1);
            $super_manager = $super_manager[0];

            $this->initAuthSetting($super_manager['nickname']);
            $this->initMailerSetting();
            $this->initCategorySetting();
            $this->initFileSetting();
            $this->initVideoStorage($formData);

            return $this->redirect($this->generateUrl('install_welcome')); 
        }
        
        return $this->render("TopxiaWebBundle:Install:init-system.html.twig"); 
    }

    private function initVideoStorage($formData)
    {
        $videoSetting = array();
        $videoSetting['upload_mode'] = $formData['upload_mode'];
        $videoSetting['cloud_access_key'] = $formData['cloud_access_key'];
        $videoSetting['cloud_secret_key'] = $formData['cloud_secret_key'];
        $videoSetting['cloud_bucket'] = $formData['cloud_bucket'];
        $this->getSettingService()->set('video', $videoSetting);
    }

    public function welcomeAction(Request $request)
    {
        $this->createTeacherNavigation();
        $this->createAboutUsNavigation();
        $this->createQuestionNavigation();
        $this->createHomeTopBanner();
        $lockFile = "{$this->container->getParameter('kernel.root_dir')}/../web/install/install.lock";
        file_put_contents($lockFile, '');
        return $this->render("TopxiaWebBundle:Install:welcome.html.twig"); 
    }

    private function  createHomeTopBanner()
    {
        $super_manager = $this->getUserService()->searchUsers(array('roles'=>'ROLE_SUPER_ADMIN'), array('createdTime', 'DESC'), 0, 1);
        $super_manager = $super_manager[0];
        $body = <<<'EOD'
<a href=""><img src="/assets/img/placeholder/carousel-1200x256-1.png" /></a>
<a href="#"><img src="/assets/img/placeholder/carousel-1200x256-2.png" /></a>
<a href="#"><img src="/assets/img/placeholder/carousel-1200x256-3.png" /></a>
EOD;
        $homeTopBanner = $this->getBlockService()->createBlock(array(
            'code'=>'home_top_banner',
            'title'=>'网站首页-顶部-图片轮播 '
            ));
        $this->getBlockService()->updateBlock($homeTopBanner['id'], array('content'=>$body));
    }

    private function createAboutUsNavigation()
    {
        $super_manager = $this->getUserService()->searchUsers(array('roles'=>'ROLE_SUPER_ADMIN'), array('createdTime', 'DESC'), 0, 1);
        $super_manager = $super_manager[0];
        $this->getNavigationService()->createNavigation(array(
            'name'=>'关于我们', 
            'url'=>'/page/aboutus', 
            'sequence' => 2,
            'isNewWin'=>0,
            'isOpen'=>1,
            'type'=>'top'));
        $this->getContentService()->createContent(array(
            'title'=>'关于我们',
            'type'=>'page',
            'alias'=>'aboutus',
            'body'=>'关于我们: <br> 简介： <br> 招聘：<br> 联系方式：<br> ',
            'template'=>'default',
            'status'=>'published',
            'createdTime'=>time(),
            'publishedTime'=>time(),
            'userId'=>$super_manager['id'],
            'promoted'=>0));
    }

    private function createQuestionNavigation()
    {
        $super_manager = $this->getUserService()->searchUsers(array('roles'=>'ROLE_SUPER_ADMIN'), array('createdTime', 'DESC'), 0, 1);
        $super_manager = $super_manager[0];
        $this->getNavigationService()->createNavigation(array(
            'name'=>'常见问题', 
            'url'=>'/page/questions', 
            'sequence' => 2,
            'isNewWin'=>0,
            'isOpen'=>1,
            'type'=>'top'));
        $body = <<<'EOD'
常见问题：<br>
<strong>入学流程？</strong><br>
注册网站会员帐号才能进行其他操作，如购买课程，学习课程，参与讨论，浏览提问等权限，不注册的用户只能观看免费的课时。
这里没有复杂的入学流程，根据课程介绍，选定课程付款即可随时随地进行学习。<br><br>
<strong>如何付款？</strong><br>
本站所有课程支持支付宝付款。<br><br>
<strong>购课流程？</strong><br>
&nbsp;&nbsp;&nbsp;&nbsp;1）注册，登录；<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;2）选择要购买的课程；<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;3）点击购买课程；<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;4）选择支付方式并支付；<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;5）购买完成后，即可浏览课程内容，在讨论区发帖。您也可以进入“我的课程”，查看自己的学习情况；<br><br>
<strong>学习流程是怎样的？</strong><br>
学员自主点播课程进行学习，遇到不懂的问题，及时提问，本课程的老师即会马上进行回答。也可以在课程的讨论区与其他学员讨论问题，上传自己的作品等。<br>
EOD;
        $this->getContentService()->createContent(array(
            'title'=>'常见问题',
            'type'=>'page',
            'alias'=>'questions',
            'body'=>$body,
            'template'=>'default',
            'status'=>'published',
            'createdTime'=>time(),
            'publishedTime'=>time(),
            'userId'=>$super_manager['id'],
            'promoted'=>0));
    }

    private function createTeacherNavigation()
    {
        $this->getNavigationService()->createNavigation(array(
            'name'=>'师资力量', 
            'url'=>'/teacher', 
            'sequence' => 1,
            'isNewWin'=>0,
            'isOpen'=>1,
            'type'=>'top'));
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
        $this->getUserService()->changeUserRoles($user['id'], array('ROLE_TEACHER', 'ROLE_SUPER_ADMIN'));
        $this->authenticateUser($this->getUserService()->getUser($user['id']));
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

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
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

    protected function getContentService()
    {
        return $this->getServiceKernel()->createService('Content.ContentService');
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }

}

