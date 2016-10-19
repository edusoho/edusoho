<?php
// 尚存在问题：
// 1.vendor不存在导致升级检测失败报错
// 2.topxia:build命令没有打包新的api目录，打包6.5.5时需要修改该命令
// 3.执行打包命令需要在项目根目录下存在installFiles文件夹
// 4.如果遇到要打版本不同的问题，检查
// web/install/edusoho_init.sql中的cloud_app数据的版本，
// systeminfo，
// app/config/config.yml，
// CHANGELOG各自的版本
// 5.包太大可能导致上传脚本失败
// 6.打包代码都在feature/install-data

require __DIR__.'/../../vendor2/autoload.php';

$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig   = new Twig_Environment($loader, array(
    'cache' => false
));

define("INSTALL_URI", "\/install\/start-install.php");

$twig->addGlobal('edusho_version', \Topxia\System::VERSION);

$step         = intval(empty($_GET['step']) ? 0 : $_GET['step']);
$init_data    = intval(empty($_GET['init_data']) ? 0 : $_GET['init_data']);
$functionName = 'install_step'.$step;

$functionName($init_data);

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\KeyApplier;
use Symfony\Component\HttpFoundation\ParameterBag;

function check_installed()
{
    if (array_key_exists('nokey', $_GET)) {
        setcookie('nokey', 1);
        $_COOKIE['nokey'] = 1;
    }

    if (file_exists(__DIR__.'/../../app/data/install.lock')) {
        exit('already install.');
    }
}

function install_step0($init_data = 0)
{
    check_installed();

    global $twig;
    echo $twig->render('step-0.html.twig', array('step' => 0));
}

function install_step1($init_data = 0)
{
    check_installed();
    global $twig;

    $pass = true;

    $env                        = array();
    $env['os']                  = PHP_OS;
    $env['phpVersion']          = PHP_VERSION;
    $env['phpVersionOk']        = version_compare(PHP_VERSION, '5.3.0') >= 0;
    $env['pdoMysqlOk']          = extension_loaded('pdo_mysql');
    $env['uploadMaxFilesize']   = ini_get('upload_max_filesize');
    $env['uploadMaxFilesizeOk'] = intval($env['uploadMaxFilesize']) >= 2;
    $env['postMaxsize']         = ini_get('post_max_size');
    $env['postMaxsizeOk']       = intval($env['postMaxsize']) >= 8;
    $env['maxExecutionTime']    = ini_get('max_execution_time');
    $env['maxExecutionTimeOk']  = ini_get('max_execution_time') >= 30;
    $env['mbstringOk']          = extension_loaded('mbstring');
    $env['gdOk']                = extension_loaded('gd');
    $env['curlOk']              = extension_loaded('curl');

    if (!$env['phpVersionOk'] ||
        !$env['pdoMysqlOk'] ||
        !$env['uploadMaxFilesizeOk'] ||
        !$env['postMaxsizeOk'] ||
        !$env['maxExecutionTimeOk'] ||
        !$env['mbstringOk'] ||
        !$env['curlOk'] ||
        !$env['gdOk']
    ) {
        $pass = false;
    }

    $paths = array(
        'app/config/parameters.yml',
        'app/data/udisk',
        'app/data/private_files',
        'web/files',
        'web/install',
        'app/cache',
        'app/data',
        'app/logs'
    );

    $checkedPaths = array();

    foreach ($paths as $path) {
        $checkedPath = __DIR__.'/../../'.$path;
        $checked     = is_executable($checkedPath) && is_writable($checkedPath) && is_readable($checkedPath);

        if (PHP_OS == 'WINNT') {
            $checked = true;
        }

        if (!$checked) {
            $pass = false;
        }

        $checkedPaths[$path] = $checked;
    }

    $safemode = ini_get('safe_mode');

    if ($safemode == 'On') {
        $pass = false;
    }

    echo $twig->render('step-1.html.twig', array(
        'step'     => 1,
        'env'      => $env,
        'paths'    => $checkedPaths,
        'safemode' => $safemode,
        'pass'     => $pass
    ));
}

function install_step2($init_data = 0)
{
    check_installed();
    global $twig;

    $error = null;
    $post  = array();

    if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
        $post          = $_POST;
        $post['index'] = empty($_GET['index']) ? 0 : $_GET['index'];
        $replace       = empty($post['database_replace']) ? false : true;
        $result        = _create_database($post, $replace);

        echo json_encode($result);
        exit();
    }

    echo $twig->render('step-2.html.twig', array(
        'step'  => 2,
        'error' => $error,
        'post'  => $post
    ));
}

function install_step3($init_data = 0)
{
    check_installed();
    global $twig;

    $connection = _create_connection();

    $serviceKernel = ServiceKernel::create('prod', true);
    $serviceKernel->setParameterBag(new ParameterBag(array(
        'kernel.root_dir' => realpath(__DIR__.'/../../app')
    )));
    $serviceKernel->setConnection($connection);

    $error = null;

    if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
        $init = new SystemInit();
        $connection->beginTransaction();
        try {
            if (!empty($init_data)) {
                $connection->exec("delete from `user` where id=1;");
                $connection->exec("delete from `user_profile` where id=1;");
            }

            $admin = $init->initAdmin($_POST);
            if (empty($init_data)) {
                $init->initTag();
                $init->initCategory();
                $init->initFile();
                $init->initPages();
                $init->initNavigations();
                $init->initBlocks();
                $init->initThemes();
                $init->initSetting($admin);
                $init->initCrontabJob();
                $init->initOrg();
                $init->initRole();
            } else {
                $init->deleteKey();
                $connection->exec("update `user_profile` set id = 1 where id = (select id from `user` where nickname = '".$_POST['nickname']."');");
                $connection->exec("update `user` set id = 1 where nickname = '".$_POST['nickname']."';");
            }

            $init->initFolders();
            $init->initLockFile();
            $connection->commit();
            header("Location: start-install.php?step=4");
            exit();
        } catch (\Exception $e) {
            echo $e->getMessage();
            $connection->rollBack();
        }
    }

    echo $twig->render('step-3.html.twig', array(
        'step'    => 3,
        'error'   => $error,
        'request' => $_POST
    ));
}

function install_step4($init_data = 0)
{
    global $twig;

    echo $twig->render('step-4.html.twig', array(
        'step' => 4
    ));
}

function install_step5($init_data = 0)
{
    try {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__);
    } catch (\Exception $e) {
    }

    header("Location: ../app.php/");
    exit();
}

function install_step888()
{
    $userAgent      = 'EduSoho Install Client 1.0';
    $connectTimeout = 10;
    $timeout        = 10;
    $url            = "http://open.edusoho.com/api/v1/block/two_dimension_code";
    $curl           = curl_init();
    curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_URL, $url);
    $response = curl_exec($curl);
    curl_close($curl);
    echo $response;
}

/**
 * 生产Key
 */
function install_step999($init_data = 0)
{
    if (empty($_COOKIE['nokey'])) {
        if (empty($_SESSION)) {
            session_start();
        }

        $connection    = _create_connection();
        $serviceKernel = ServiceKernel::create('prod', true);
        $serviceKernel->setParameterBag(new ParameterBag(array(
            'kernel.root_dir' => realpath(__DIR__.'/../../app')
        )));

        $serviceKernel->setConnection($connection);

        $init = new SystemInit();

        $key = $init->initKey();

        echo json_encode($key);
    } else {
        echo json_encode(array(
            'accessKey' => '__NOKEY__',
            'secretKey' => '__NOKEY__'
        ));
    }
}

function _create_database($config, $replace)
{
    try {
        $pdo = new PDO("mysql:host={$config['database_host']};port={$config['database_port']}", "{$config['database_user']}", "{$config['database_password']}");
        $pdo->exec("SET NAMES utf8");

        //仅在第一次进来时初始化数据库表结构

        if (empty($config['index'])) {
            $result = $pdo->exec("create database `{$config['database_name']}`;");

            if (empty($result) && !$replace) {
                return "数据库{$config['database_name']}已存在，创建失败，请删除或者勾选覆盖数据库之后再安装！";
            }

            $pdo->exec("USE `{$config['database_name']}`;");

            $sql    = file_get_contents('./edusoho.sql');
            $result = $pdo->exec($sql);

            if ($result === false) {
                return "创建数据库表结构失败，请删除数据库后重试！";
            }

            if (empty($config["database_init"])) {
                _create_config($config);
                return array('success' => true);
            }
        }

        //每次进来都执行一个演示数据初始化文件

        if (!empty($config["database_init"]) && $config["database_init"] == 1) {
            $index = $config['index'];

            if ($index > 0) {
                $pdo->exec("USE `{$config['database_name']}`;");
            }

            _init_data($pdo, $config, $index);
            $index++;
            $filesystem = new Filesystem();

            if (!$filesystem->exists('edusoho_init_'.$index.'.sql')) {
                _init_auto_increment($pdo, $config);
                return array('success' => true);
            }

            return array('index' => $index);
        }

        return null;
    } catch (\PDOException $e) {
        return '数据库连接不上，请检查数据库服务器、用户名、密码是否正确!';
    }
}

function _init_data($pdo, $config, $index)
{
    $sql    = file_get_contents('./edusoho_init_'.$index.'.sql');
    $result = $pdo->exec($sql);
}

function _init_auto_increment($pdo, $config)
{
    $sql     = "show tables";
    $results = $pdo->query($sql)->fetchAll();

    foreach ($results as $result) {
        $table    = $result["0"];
        $countSql = "select count(*) from {$table}";
        $sqlPdo   = $pdo->query($countSql);

        if (!empty($sqlPdo)) {
            $count = $pdo->query($countSql)->fetchColumn(0);

            if ($count > 0) {
                $pdo->exec("alter table {$table} AUTO_INCREMENT={$count};");
            }
        }
    }

    _create_config($config);
}

function _create_config($config)
{
    $secret = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    $config = "parameters:
    database_driver: pdo_mysql
    database_host: {$config['database_host']}
    database_port: {$config['database_port']}
    database_name: {$config['database_name']}
    database_user: {$config['database_user']}
    database_password: '{$config['database_password']}'
    mailer_transport: smtp
    mailer_host: 127.0.0.1
    mailer_user: null
    mailer_password: null
    locale: zh_CN
    secret: {$secret}
    user_partner: none";

    file_put_contents(__DIR__."/../../app/config/parameters.yml", $config);
}

function _create_connection()
{
    $factory    = new \Doctrine\Bundle\DoctrineBundle\ConnectionFactory(array());
    $parameters = file_get_contents(__DIR__."/../../app/config/parameters.yml");
    $parameters = \Symfony\Component\Yaml\Yaml::parse($parameters);
    $parameters = $parameters['parameters'];

    $connection = $factory->createConnection(
        array(
            'wrapperClass'  => 'Topxia\Service\Common\Connection',
            'dbname'        => $parameters['database_name'],
            'host'          => $parameters['database_host'],
            'port'          => $parameters['database_port'],
            'user'          => $parameters['database_user'],
            'password'      => $parameters['database_password'],
            'charset'       => 'UTF8',
            'driver'        => $parameters['database_driver'],
            'driverOptions' => array()
        )
        ,
        new \Doctrine\DBAL\Configuration(),
        null,
        array('enum' => 'string')
    );

    $connection->exec("SET NAMES utf8");

    return $connection;
}

class SystemInit
{
    public function initAdmin($user)
    {
        $user['emailVerified'] = 1;
        $user                  = $user = $this->getUserService()->register($user);
        $user['roles']         = array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_SUPER_ADMIN');
        $user['currentIp']     = '127.0.0.1';

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        ServiceKernel::instance()->setCurrentUser($currentUser);

        $this->getUserService()->changeUserRoles($user['id'], array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_SUPER_ADMIN'));
        return $this->getUserService()->getUser($user['id']);
    }

    public function deleteKey()
    {
        $settings = $this->getSettingService()->get('storage', array());

        if (!empty($settings['cloud_key_applied'])) {
            unset($settings['cloud_access_key']);
            unset($settings['cloud_secret_key']);
            unset($settings['cloud_key_applied']);
            $this->getSettingService()->set('storage', $settings);
        }
    }

    public function initKey()
    {
        $settings = $this->getSettingService()->get('storage', array());

        if (!empty($settings['cloud_key_applied'])) {
            return array(
                'accessKey' => '您的Key已生成，请直接进入系统',
                'secretKey' => '---'
            );
        }

        $applier = new KeyApplier();

        $users = $this->getUserService()->searchUsers(array('roles' => 'ROLE_SUPER_ADMIN'), array('createdTime', 'DESC'), 0, 1);

        if (empty($users) || empty($users[0])) {
            return array('error' => '管理员账号不存在，创建Key失败');
        }

        $keys = $applier->applyKey($users[0], 'opensource', 'install');

        if (empty($keys['accessKey']) || empty($keys['secretKey'])) {
            return array('error' => 'Key生成失败，请检查服务器网络后，重试！');
        }

        $settings['cloud_access_key']  = $keys['accessKey'];
        $settings['cloud_secret_key']  = $keys['secretKey'];
        $settings['cloud_key_applied'] = 1;

        $this->getSettingService()->set('storage', $settings);

        return $keys;
    }

    public function initSetting($user)
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

        $settings = array(
            'refund'         => array(
                'maxRefundDays'       => 10,
                'applyNotification'   => '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。',
                'successNotification' => '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。',
                'failedNotification'  => '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。'
            ),
            'article'        => array(
                'name' => '资讯频道', 'pageNums' => 20
            ),
            'site'           => array(
                'name'              => $_POST['sitename'],
                'slogan'            => '',
                'url'               => '',
                'logo'              => '',
                'seo_keywords'      => '',
                'seo_description'   => '',
                'master_email'      => $_POST['email'],
                'icp'               => '',
                'analytics'         => '',
                'status'            => 'open',
                'closed_note'       => '',
                'homepage_template' => 'less'
            ),
            'developer'      => array('cloud_api_failover' => 1),
            'auth'           => array(
                'register_mode'          => 'email',
                'email_activation_title' => '请激活您的{{sitename}}账号',
                'email_activation_body'  => trim($emailBody),
                'welcome_enabled'        => 'opened',
                'welcome_sender'         => $user['nickname'],
                'welcome_methods'        => array(),
                'welcome_title'          => '欢迎加入{{sitename}}',
                'welcome_body'           => '您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。'
            ),
            'mailer'         => array(
                'enabled'  => 0,
                'host'     => 'smtp.example.com',
                'port'     => '25',
                'username' => 'user@example.com',
                'password' => '',
                'from'     => 'user@example.com',
                'name'     => $_POST['sitename']
            ),
            'payment'        => array(
                'enabled'        => 0,
                'bank_gateway'   => 'none',
                'alipay_enabled' => 0,
                'alipay_key'     => '',
                'alipay_secret'  => ''
            ),
            'storage'        => array(
                'upload_mode'           => 'local',
                'cloud_access_key'      => '',
                'cloud_secret_key'      => '',
                'cloud_api_server'      => 'http://api.edusoho.net',
                'enable_playback_rates' => 0
            ),
            'post_num_rules' => array(
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
            ),
            'default'        => array(
                'user_name'    => '学员',
                'chapter_name' => '章',
                'part_name'    => '节'
            ),
            'coin'           => array(
                'coin_enabled'        => 0,
                'cash_model'          => 'none',
                'cash_rate'           => 1,
                'coin_name'           => '虚拟币',
                'coin_content'        => '',
                'coin_picture'        => '',
                'coin_picture_50_50'  => '',
                'coin_picture_30_30'  => '',
                'coin_picture_20_20'  => '',
                'coin_picture_10_10'  => '',
                'charge_coin_enabled' => ''
            ),
            'magic'          => array(
                'export_allow_count' => 100000,
                'export_limit'       => 10000,
                'enable_org'         => 0
            ),
            'cloud_sms'      => array(
                'system_remind' => 'on'
            )
        );

        foreach ($settings as $key => $value) {
            $setting = $this->getSettingService()->get($key, array());
            $setting = array_merge($value, $setting);
            $this->getSettingService()->set($key, $setting);
        }
    }

    public function initTag()
    {
        $defaultTag = $this->getTagService()->getTagByName('默认标签');

        if (!$defaultTag) {
            $this->getTagService()->addTag(array('name' => '默认标签'));
        }
    }

    public function initCategory()
    {
        $group = $this->getCategoryService()->addGroup(array(
            'name'  => '课程分类',
            'code'  => 'course',
            'depth' => 3
        ));

        $this->getCategoryService()->createCategory(array(
            'name'     => '默认分类',
            'code'     => 'default',
            'weight'   => 100,
            'groupId'  => $group['id'],
            'parentId' => 0
        ));
    }

    public function initFile()
    {
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
    }

    public function initPages()
    {
        $this->getContentService()->createContent(array(
            'title'    => '关于我们',
            'type'     => 'page',
            'alias'    => 'aboutus',
            'body'     => '',
            'template' => 'default',
            'status'   => 'published'
        ));

        $this->getContentService()->createContent(array(
            'title'    => '常见问题',
            'type'     => 'page',
            'alias'    => 'questions',
            'body'     => '',
            'template' => 'default',
            'status'   => 'published'
        ));
    }

    public function initNavigations()
    {
        $this->getNavigationService()->createNavigation(array(
            'name'     => '师资力量',
            'url'      => 'teacher',
            'sequence' => 1,
            'isNewWin' => 0,
            'isOpen'   => 1,
            'type'     => 'top'
        ));

        $this->getNavigationService()->createNavigation(array(
            'name'     => '常见问题',
            'url'      => 'page/questions',
            'sequence' => 2,
            'isNewWin' => 0,
            'isOpen'   => 1,
            'type'     => 'top'
        ));

        $this->getNavigationService()->createNavigation(array(
            'name'     => '关于我们',
            'url'      => 'page/aboutus',
            'sequence' => 2,
            'isNewWin' => 0,
            'isOpen'   => 1,
            'type'     => 'top'
        ));
    }

    public function initThemes()
    {
        $this->getSettingService()->set('theme', array('uri' => 'jianmo'));
    }

    public function initBlocks()
    {
        $themeDir = realpath(__DIR__.'/../themes/');

        $metaFiles = array(
            'system'  => "{$themeDir}/block.json",
            'default' => "{$themeDir}/default/block.json",
            'autumn'  => "{$themeDir}/autumn/block.json",
            'jianmo'  => "{$themeDir}/jianmo/block.json"
        );

        foreach ($metaFiles as $category => $file) {
            $metas = file_get_contents($file);
            $metas = json_decode($metas, true);

            foreach ($metas as $code => $meta) {
                $data = array();

                foreach ($meta['items'] as $key => $item) {
                    $data[$key] = $item['default'];
                }

                $filename = __DIR__.'/blocks/'."block-".md5($code).'.html';

                if (file_exists($filename)) {
                    $content = file_get_contents($filename);
                    $content = preg_replace_callback('/(<img[^>]+>)/i', function ($matches) {
                        preg_match_all('/<\s*img[^>]*src\s*=\s*["\']?([^"\']*)/is', $matches[0], $srcs);
                        preg_match_all('/<\s*img[^>]*alt\s*=\s*["\']?([^"\']*)/is', $matches[0], $alts);
                        $URI = preg_replace('/'.INSTALL_URI.'.*/i', '', $_SERVER['REQUEST_URI']);
                        $src = preg_replace('/\b\?[\d]+.[\d]+.[\d]+/i', '', $srcs[1][0]);
                        $src = $URI.trim($src);

                        $img = "<img src='{$src}'";

                        if (isset($alts[1][0])) {
                            $alt = $alts[1][0];
                            $img .= " alt='{$alt}'>";
                        } else {
                            $img .= ">";
                        }

                        return $img;
                    }, $content);
                } else {
                    $content = '';
                }

                $template = $this->getBlockService()->createBlockTemplate(array(
                    'title'        => $meta['title'],
                    'mode'         => 'template',
                    'templateName' => $meta['templateName'],
                    'content'      => $content,
                    'code'         => $code,
                    'meta'         => $meta,
                    'data'         => $data,
                    'category'     => $category
                ));

                $this->getBlockService()->createBlock(array(
                    'blockTemplateId' => $template['id'],
                    'code'            => $code,
                    'content'         => $content,
                    'data'            => $data
                ));
            }
        }
    }

    public function initCrontabJob()
    {
        $this->getCrontabService()->createJob(array(
            'name'            => 'CancelOrderJob',
            'cycle'           => 'everyhour',
            'jobClass'        => 'Topxia\\Service\\Order\\Job\\CancelOrderJob',
            'nextExcutedTime' => time(),
            'jobParams'       => '{}',
            'createdTime'     => time()
        ));

        $this->getCrontabService()->createJob(array(
            'name'            => 'DeleteExpiredTokenJob',
            'cycle'           => 'everyhour',
            'jobClass'        => 'Topxia\\Service\\User\\Job\\DeleteExpiredTokenJob',
            'jobParams'       => '{}',
            'nextExcutedTime' => time(),
            'createdTime'     => time()
        ));

        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    public function initOrg()
    {
        $org = array(
            'name' => '全站',
            'code' => 'FullSite'
        );
        $this->getOrgService()->createOrg($org);
    }

    public function initFolders()
    {
        $folders = array(
            __DIR__.'/../../app/data/udisk',
            __DIR__.'/../../app/data/private_files',
            __DIR__.'/../../web/files'
        );

        $filesystem = new Filesystem();

        foreach ($folders as $folder) {
            if (!$filesystem->exists($folder)) {
                $filesystem->mkdir($folder);
            }
        }
    }

    public function initLockFile()
    {
        file_put_contents(__DIR__.'/../../app/data/install.lock', '');

    }

    public function initRole()
    {
        $this->getRoleService()->refreshRoles();
    }

    private function getCrontabService()
    {
        return ServiceKernel::instance()->createService('Crontab.CrontabService');
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    private function getCategoryService()
    {
        return ServiceKernel::instance()->createService('Taxonomy.CategoryService');
    }

    private function getTagService()
    {
        return ServiceKernel::instance()->createService('Taxonomy.TagService');
    }

    private function getFileService()
    {
        return ServiceKernel::instance()->createService('Content.FileService');
    }

    protected function getContentService()
    {
        return ServiceKernel::instance()->createService('Content.ContentService');
    }

    protected function getBlockService()
    {
        return ServiceKernel::instance()->createService('Content.BlockService');
    }

    protected function getNavigationService()
    {
        return ServiceKernel::instance()->createService('Content.NavigationService');
    }

    protected function getOrgService()
    {
        return ServiceKernel::instance()->createService('Org:Org.OrgService');
    }

    protected function getRoleService()
    {
        return ServiceKernel::instance()->createService('Permission:Role.RoleService');
    }

    protected function postRequest($url, $params)
    {
        $userAgent = 'EduSoho Install Client 1.0';

        $connectTimeout = 10;

        $timeout = 10;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url);

        // curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
