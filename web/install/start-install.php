<?php

date_default_timezone_set('Asia/Shanghai');
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

require __DIR__.'/../../bootstrap/bootstrap_install.php';

$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig = new Twig_Environment($loader, array(
    'cache' => false,
));

$twig->addGlobal('edusoho_version', \AppBundle\System::VERSION);

$step = intval(empty($_GET['step']) ? 0 : $_GET['step']);
$init_data = 1; //写入演示数据
$functionName = 'install_step'.$step;

$functionName($init_data);

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Biz\Crontab\SystemCrontabInitializer;

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

    $env = array();
    $env['os'] = PHP_OS;
    $env['phpVersion'] = PHP_VERSION;
    $env['phpVersionOk'] = version_compare(PHP_VERSION, '5.5.0') >= 0;
    $env['pdoMysqlOk'] = extension_loaded('pdo_mysql');
    $env['uploadMaxFilesize'] = ini_get('upload_max_filesize');
    $env['uploadMaxFilesizeOk'] = intval($env['uploadMaxFilesize']) >= 2;
    $env['postMaxsize'] = ini_get('post_max_size');
    $env['postMaxsizeOk'] = intval($env['postMaxsize']) >= 8;
    $env['maxExecutionTime'] = ini_get('max_execution_time');
    $env['maxExecutionTimeOk'] = ini_get('max_execution_time') >= 30;
    $env['mbstringOk'] = extension_loaded('mbstring');
    $env['gdOk'] = extension_loaded('gd');
    $env['curlOk'] = extension_loaded('curl');

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
        'app/logs',
    );

    $checkedPaths = array();

    foreach ($paths as $path) {
        $checkedPath = __DIR__.'/../../'.$path;
        $checked = is_executable($checkedPath) && is_writable($checkedPath) && is_readable($checkedPath);

        if (PHP_OS == 'WINNT') {
            $checked = true;
        }

        if (!$checked) {
            $pass = false;
        }

        $checkedPaths[$path] = $checked;
    }

    $safemode = ini_get('safe_mode');

    if ('On' == $safemode) {
        $pass = false;
    }
    $result = _checkWebRoot();
    if (false === $result) {
        $pass = false;
    }
    echo $twig->render('step-1.html.twig', array(
        'step' => 1,
        'env' => $env,
        'paths' => $checkedPaths,
        'safemode' => $safemode,
        'pass' => $pass,
        'root' => $result,
    ));
}

function install_step2($init_data = 0)
{
    check_installed();
    global $twig;

    $error = null;
    $post = array();

    if ('POST' == strtoupper($_SERVER['REQUEST_METHOD'])) {
        $post = $_POST;
        $post['index'] = empty($_GET['index']) ? 0 : $_GET['index'];
        $replace = empty($post['database_replace']) ? false : true;
        $result = _create_database($post, $replace);

        echo json_encode($result);
        exit();
    }

    echo $twig->render('step-2.html.twig', array(
        'step' => 2,
        'error' => $error,
        'post' => $post,
    ));
}

function install_step3($init_data = 0)
{
    check_installed();
    global $twig;

    global $biz;

    $error = null;

    if ('POST' == strtoupper($_SERVER['REQUEST_METHOD'])) {
        $biz['db']->beginTransaction();
        $installLogFd = @fopen($biz['log_directory'].'/install.log', 'w');
        $output = new \Symfony\Component\Console\Output\StreamOutput($installLogFd);
        $initializer = new \AppBundle\Common\SystemInitializer($output);
        $biz['user'] = new \Biz\User\AnonymousUser();
        $biz['user.register'] = new \Biz\User\Register\RegisterFactory($biz);
        $biz['user.register.email'] = new \Biz\User\Register\Impl\EmailRegistDecoderImpl($biz);
        try {
            if (!empty($init_data)) {
                $biz['db']->exec('update user set roles ="|ROLE_USER|ROLE_TEACHER|" where id = 1');
            }
            $admin = $initializer->initAdminUser($_POST);
            _init_setting($admin);
            $service = ServiceKernel::instance()->createService('System:SettingService');
            $settings = $service->get('storage', array());
            if (!empty($settings['cloud_key_applied'])) {
                unset($settings['cloud_access_key']);
                unset($settings['cloud_secret_key']);
                unset($settings['cloud_key_applied']);
                $service->set('storage', $settings);
            }

            $initializer->initFolders();
            $initializer->initLockFile();
            SystemCrontabInitializer::init();
            $biz['db']->commit();
            header('Location: start-install.php?step=4');
            exit();
        } catch (\Exception $e) {
            echo $e->getMessage();
            $biz['db']->rollBack();
            @fclose($installLogFd);
        }
    }

    echo $twig->render('step-3.html.twig', array(
        'step' => 3,
        'error' => $error,
        'request' => $_POST,
    ));
}

function install_step4($init_data = 0)
{
    global $twig;

    echo $twig->render('step-4.html.twig', array(
        'step' => 4,
    ));
}

function install_step5($init_data = 0)
{
    try {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__);
    } catch (\Exception $e) {
    }

    header('Location: ../');
    exit();
}

function install_step888()
{
    $userAgent = 'EduSoho Install Client 1.0';
    $connectTimeout = 10;
    $timeout = 10;
    $url = 'http://open.edusoho.com/api/v1/block/two_dimension_code';
    $curl = curl_init();
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

        $key = _initKey();

        echo json_encode($key);
    } else {
        echo json_encode(array(
            'accessKey' => '__NOKEY__',
            'secretKey' => '__NOKEY__',
        ));
    }
}

function _create_database($config, $replace)
{
    try {
        $pdo = new PDO("mysql:host={$config['database_host']};port={$config['database_port']}", "{$config['database_user']}", "{$config['database_password']}");
        $pdo->exec('SET NAMES utf8');

        $database_init = 1; //写入演示数据
        //仅在第一次进来时初始化数据库表结构
        if (empty($config['index'])) {
            $result = $pdo->exec("create database `{$config['database_name']}`;");

            if (empty($result) && !$replace) {
                return "数据库{$config['database_name']}已存在，创建失败，请删除或者勾选覆盖数据库之后再安装！";
            }

            $pdo->exec("USE `{$config['database_name']}`;");

            $sql = file_get_contents('./edusoho.sql');
            $result = $pdo->exec($sql);

            if (false === $result) {
                return "创建数据库失败，查看<a href='http://www.qiqiuyu.com/faq/585/detail' target='_blank'>解决方案</a>";
            }

            /*if (empty($config['database_init'])) {
                _create_config($config);

                return array('success' => true);
            }*/
        }

        //每次进来都执行一个演示数据初始化文件

        if ($database_init) {
            $index = $config['index'];

            if ($index > 0) {
                $pdo->exec("USE `{$config['database_name']}`;");
            }

            _init_data($pdo, $config, $index);
            ++$index;
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
    $sql = file_get_contents('./edusoho_init_'.$index.'.sql');
    $result = $pdo->exec($sql);
}

function _init_auto_increment($pdo, $config)
{
    $sql = 'show tables';
    $results = $pdo->query($sql)->fetchAll();

    foreach ($results as $result) {
        $table = $result['0'];
        $countSql = "select count(*) from {$table}";
        $sqlPdo = $pdo->query($countSql);

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
    $server = $_SERVER['SERVER_NAME'];
    if (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) {
        $server = 'https://'.$server;
    } else {
        $server = 'http://'.$server;
    }
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
    webpack_base_url: {$server}
    user_partner: none";

    file_put_contents(__DIR__.'/../../app/config/parameters.yml', $config);
}

function _init_setting($user)
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
        'refund' => array(
            'maxRefundDays' => 10,
            'applyNotification' => '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。',
            'successNotification' => '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。',
            'failedNotification' => '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。',
        ),
        'article' => array(
            'name' => '资讯频道', 'pageNums' => 20,
        ),
        'site' => array(
            'name' => $_POST['sitename'],
            'slogan' => '',
            'url' => '',
            'logo' => '',
            'seo_keywords' => '',
            'seo_description' => '',
            'master_email' => $_POST['email'],
            'icp' => '',
            'analytics' => '',
            'status' => 'open',
            'closed_note' => '',
            'homepage_template' => 'less',
        ),
        'developer' => array('cloud_api_failover' => 1),
        'auth' => array(
            'register_mode' => 'email',
            'email_activation_title' => '请激活您的{{sitename}}帐号',
            'email_activation_body' => trim($emailBody),
            'welcome_enabled' => 'opened',
            'welcome_sender' => $user['nickname'],
            'welcome_methods' => array(),
            'welcome_title' => '欢迎加入{{sitename}}',
            'welcome_body' => '您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。',
        ),
        'mailer' => array(
            'enabled' => 0,
            'host' => 'smtp.example.com',
            'port' => '25',
            'username' => 'user@example.com',
            'password' => '',
            'from' => 'user@example.com',
            'name' => $_POST['sitename'],
        ),
        'payment' => array(
            'enabled' => 0,
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
        ),
        'storage' => array(
            'upload_mode' => 'local',
            'cloud_access_key' => '',
            'cloud_secret_key' => '',
            'cloud_api_server' => 'http://api.edusoho.net',
            'enable_playback_rates' => 0,
        ),
        'post_num_rules' => array(
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
        ),
        'default' => array(
            'user_name' => '学员',
            'chapter_name' => '章',
            'part_name' => '节',
        ),
        'coin' => array(
            'coin_enabled' => 0,
            'cash_model' => 'none',
            'cash_rate' => 1,
            'coin_name' => '虚拟币',
            'coin_content' => '',
            'coin_picture' => '',
            'coin_picture_50_50' => '',
            'coin_picture_30_30' => '',
            'coin_picture_20_20' => '',
            'coin_picture_10_10' => '',
            'charge_coin_enabled' => '',
        ),
        'magic' => array(
            'export_allow_count' => 100000,
            'export_limit' => 10000,
            'enable_org' => 0,
        ),
        'cloud_sms' => array(
            'system_remind' => 'on',
        ),
    );

    $service = ServiceKernel::instance()->createService('System:SettingService');
    foreach ($settings as $key => $value) {
        $setting = $service->get($key, array());
        $setting = array_merge($setting, $value);
        $service->set($key, $setting);
    }
}

function _initKey()
{
    global $biz;
    $currentUser = new \Biz\User\AnonymousUser();

    $biz['user'] = $currentUser;
    $settingService = $biz->service('System:SettingService');

    $settings = $settingService->get('storage', array());

    if (!empty($settings['cloud_key_applied'])) {
        return array(
            'accessKey' => '您的Key已生成，请直接进入系统',
            'secretKey' => '---',
        );
    }

    $applier = new \Biz\CloudPlatform\KeyApplier();

    $userService = $biz->service('User:UserService');
    $users = $userService->searchUsers(array('roles' => 'ROLE_SUPER_ADMIN'), array('createdTime' => 'DESC'), 0, 1);

    if (empty($users) || empty($users[0])) {
        return array('error' => '管理员帐号不存在，创建Key失败');
    }

    $keys = $applier->applyKey($users[0], 'opensource', 'install');

    if (empty($keys['accessKey']) || empty($keys['secretKey'])) {
        return array('error' => 'Key生成失败，请检查服务器网络后，重试！');
    }

    $settings['cloud_access_key'] = $keys['accessKey'];
    $settings['cloud_secret_key'] = $keys['secretKey'];
    $settings['cloud_key_applied'] = 1;

    $settingService->set('storage', $settings);

    return $keys;
}

function _checkWebRoot()
{
    $host = $_SERVER['HTTP_REFERER'];
    $hostArray = explode('/', $host);
    if (in_array('web', $hostArray)) {
        return false;
    }

    return true;
}
