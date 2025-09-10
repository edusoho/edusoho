<?php

use Topxia\Service\Common\ServiceKernel;

class FileMigrate extends AbstractMigrate
{
    public function update($page)
    {

        $this->rebuildCloudSearchIndex();
        $this->upgradeEduSohoApp();
        $this->copyAndOverwriteUpgradeFiles();
        $this->getConnection()->commit();
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        $this->logger('8.0.0','warnnig', 'FileMigrate  removing cache folder');
        $filesystem->remove($this->kernel->getParameter('kernel.root_dir') .'/cache');
        $this->logger('8.0.0','warnnig', 'FileMigrate  removing upgrade.lock ');
        $lockFile = $this->kernel->getParameter('kernel.root_dir') . '/data/upgrade.lock';
        @unlink($lockFile);
        echo json_encode(array('status' => 'ok'));
        $this->logger('8.0.0','warnnig', 'FileMigrate  complete ');
        exit(0);
    }

    private function copyAndOverwriteUpgradeFiles()
    {
        $sourceDir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0/source';
        $edusohoDir = $this->kernel->getParameter('kernel.root_dir') . '/../';
        $this->logger('8.0.0','warnnig', 'copyAndOverwriteUpgradeFiles  copy api folder');
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $filesystem->mirror($sourceDir.'/api', $edusohoDir.'/api', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $this->logger('8.0.0','warnnig', 'copyAndOverwriteUpgradeFiles  copy app folder');
        $this->copyAppDir();

        $this->logger('8.0.0','warnnig', 'copyAndOverwriteUpgradeFiles  copy bootstrap folder');
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $filesystem->mirror($sourceDir.'/bootstrap', $edusohoDir.'/bootstrap', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
        $this->logger('8.0.0','warnnig', 'copyAndOverwriteUpgradeFiles  copy CHANGELOG');
        $filesystem->copy($sourceDir.'/CHANGELOG', $edusohoDir.'/CHANGELOG', true);

        $this->logger('8.0.0','warnnig', 'copyAndOverwriteUpgradeFiles  copy src folder');
        $this->copySrcDir();

        $this->logger('8.0.0','warnnig', 'copyAndOverwriteUpgradeFiles  copy vendor folder');
        $filesystem->mirror($sourceDir.'/vendor', $edusohoDir.'/vendor', null, array(
            'override' => true,
            'delete' => true,
            'copy_on_windows' => true,
        ));
        $this->logger('8.0.0','warnnig', 'copyAndOverwriteUpgradeFiles  copy web folder');
        $this->copyWebDir();

        $this->logger('8.0.0','warnnig', 'copyAndOverwriteUpgradeFiles  copy is complete');
    }

    private function copyWebDir()
    {
        $sourceDir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0/source';
        $edusohoDir = $this->kernel->getParameter('kernel.root_dir') . '/../';

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $filesystem->mirror($sourceDir.'/web/assets', $edusohoDir.'/web/assets', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $filesystem->mirror($sourceDir.'/web/bundles', $edusohoDir.'/web/bundles', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $filesystem->mirror($sourceDir.'/web/themes', $edusohoDir.'/web/themes', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $filesystem->copy($sourceDir.'/web/app_dev.php', $edusohoDir.'/web/app_dev.php', true);
        $filesystem->copy($sourceDir.'/web/config.php', $edusohoDir.'/web/config.php', true);
    }

    private function copySrcDir()
    {
        $sourceDir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0/source';
        $edusohoDir = $this->kernel->getParameter('kernel.root_dir') . '/../';

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $filesystem->mirror($sourceDir.'/src/Custom', $edusohoDir.'/src/Custom', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $filesystem->mirror($sourceDir.'/src/Topxia', $edusohoDir.'/src/Topxia', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
    }

    private function copyAppDir()
    {
        $sourceDir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0/source';
        $edusohoDir = $this->kernel->getParameter('kernel.root_dir') . '/../';

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $filesystem->mirror($sourceDir.'/app/config', $edusohoDir.'/app/config', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $filesystem->copy($sourceDir.'/app/bootstrap.php.cache', $edusohoDir.'/app/bootstrap.php.cache', true);
        $filesystem->copy($sourceDir.'/app/AppKernel.php', $edusohoDir.'/app/AppKernel.php', true);
        $filesystem->copy($sourceDir.'/app/SymfonyRequirements.php', $edusohoDir.'/app/SymfonyRequirements.php', true);

        $filesystem->mirror($sourceDir.'/app/Resources/views', $edusohoDir.'/app/Resources/views', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $filesystem->mirror($sourceDir.'/app/Resources/TwigBundle', $edusohoDir.'/app/Resources/TwigBundle', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $filesystem->mirror($sourceDir.'/app/Resources/translations', $edusohoDir.'/app/Resources/translations', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
    }

    protected function getSettingService()
    {
        if ($this->isX8()) {
            return ServiceKernel::instance()->createService('System:SettingService');
        }
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    private function rebuildCloudSearchIndex()
    {
        $service = $this->getSettingService();

        $cloudSearchSetting = $service->get('cloud_search');

        if(empty($cloudSearchSetting) || empty($cloudSearchSetting['search_enabled'])){
            $this->logger('8.0.0','warnnig', 'rebuildCloudSearchIndex cloud search is not enabled ');
            return;
        }
        $this->logger('8.0.0','warnnig', 'rebuildCloudSearchIndex rebuilding search index');

        $siteSetting = $service->get('site');
        $siteUrl = $siteSetting['url'];
        if (strpos($siteUrl, 'http://') !== 0) {
            $siteUrl = 'http://'.$siteUrl;
        }

        $siteUrl = rtrim(rtrim($siteUrl), '/');

        $urls = array(
            array(
                'category' => 'course',
                'url' => $siteUrl.'/callback/cloud_search?provider=courses&cursor=0&start=0&limit=100',
            ),
            array(
                'category' => 'lesson',
                'url' => $siteUrl.'/callback/cloud_search?provider=lessons&cursor=0&start=0&limit=100',
            ),
            array(
                'category' => 'user',
                'url' => $siteUrl.'/callback/cloud_search?provider=users&cursor=0&start=0&limit=100',
            ),
            array(
                'category' => 'thread',
                'url' => $siteUrl.'/callback/cloud_search?provider=chaos_threads&cursor=0,0,0&start=0,0,0&limit=50',
            ),
            array(
                'category' => 'article',
                'url' => $siteUrl.'/callback/cloud_search?provider=articles&cursor=0&start=0&limit=100',
            ),
            array(
                'category' => 'openCourse',
                'url' => $siteUrl.'/callback/cloud_search?provider=open_courses&cursor=0&start=0&limit=100',
            ),
            array(
                'category' => 'openLesson',
                'url' => $siteUrl.'/callback/cloud_search?provider=open_course_lessons&cursor=0&start=0&limit=100',
            ),
        );
        $urls = urlencode(json_encode($urls));

        $storage = $service->get('storage', array());

        $api = new CloudAPI(array(
            'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
            'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
            'debug' => true,
        ));

        $callbackUrl = $siteUrl.'/edu_cloud/search/callback';

        $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha256');
        $sign = $encoder->encodePassword($callbackUrl, $api->getAccessKey());
        $callbackUrl .= '?sign='.rawurlencode($sign);

        try{
            $result = $api->post('/search/accounts', array('urls' => $urls, 'callback' => $callbackUrl));

            if ($result['success']) {
                $searchSetting = $service->get('cloud_search', array(
                    'search_enabled' => 1,
                    'status' => 'waiting',
                ));

                if (empty($searchSetting['type'])) {
                    $searchSetting['type'] = array(
                        'course' => 1,
                        'teacher' => 1,
                        'thread' => 1,
                        'article' => 1,
                    );
                }

                $service->set('cloud_search', $searchSetting);
            }

            $conditions = array('categorys' => 'course,user,thread,article');
            $api->post('/search/refactor_documents', $conditions);

            $openApi = new EduSohoAppClient(array(
                'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
            ));
            $log = array(
                'level' => 'info',
                'productId' => 1,
                'productName' => 'EduSoho主程序',
                'packageId' => 946,
                'type' => 'install',
                'fromVersion' => '7.5.15',
                'toVersion' => '8.0.0',
                'message' => '成功',
                'data' => '',
            );

            $openApi->submitRunLog($log);
        }catch (\Exception $exception){
            return;
        }


    }

    private function upgradeEduSohoApp()
    {
        $this->logger('8.0.0','warnnig', 'upgradeEduSohoApp update cloud_app version');
        $time = time();
        $user = $this->kernel->getCurrentUser();
        $this->exec("UPDATE cloud_app SET version = '8.0.0', fromVersion = '7.5.15', protocol = 3, updatedTime = {$time} WHERE code = 'MAIN';");
        $this->exec("INSERT INTO `cloud_app_logs` (`code`, `name`, `fromVersion`, `toVersion`, `type`, `status`, `userId`, `ip`, `message`, `createdTime`)
                               VALUES ('MAIN', 'EduSoho主系统', '7.5.15', '8.0.0', 'install', 'SUCCESS', {$user['id']}, '{$user['currentIp']}', '', {$time})");
    }
}

class CloudAPI
{
    const VERSION = 'v1';

    protected $userAgent = 'EduSoho Cloud API Client 1.0';

    protected $connectTimeout = 15;

    protected $timeout = 15;

    protected $apiUrl = 'http://api.edusoho.net';

    protected $debug = false;

    /**
     * @var string
     */
    protected $accessKey;

    /**
     * @var string
     */
    protected $secretKey;

    public function __construct(array $options)
    {
        $this->setKey($options['accessKey'], $options['secretKey']);

        if (!empty($options['apiUrl'])) {
            $this->setApiUrl($options['apiUrl']);
        }

        $this->debug = empty($options['debug']) ? false : true;
    }

    public function setApiUrl($url)
    {
        $this->apiUrl = rtrim($url, '/');

        return $this;
    }

    public function setKey($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;

        return $this;
    }

    public function getAccessKey()
    {
        return $this->accessKey;
    }

    public function post($uri, array $params = array(), array $header = array())
    {
        return $this->_request('POST', $uri, $params, $header);
    }

    public function put($uri, array $params = array(), array $header = array())
    {
        return $this->_request('PUT', $uri, $params, $header);
    }

    public function patch($uri, array $params = array(), array $header = array())
    {
        return $this->_request('PATCH', $uri, $params, $header);
    }

    public function get($uri, array $params = array(), array $header = array())
    {
        return $this->_request('GET', $uri, $params, $header);
    }

    public function delete($uri, array $params = array(), array $header = array())
    {
        return $this->_request('DELETE', $uri, $params, $header);
    }

    protected function _request($method, $uri, $params, $headers)
    {
        $requestId = substr(md5(uniqid('', true)), -16);

        $url = $this->apiUrl.'/'.self::VERSION.$uri;

        $headers[] = 'Content-type: application/json';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ($method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ($method == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ($method == 'PATCH') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        $headers[] = 'Auth-Token: '.$this->_makeAuthToken($url, $method == 'GET' ? array() : $params);
        $headers[] = 'API-REQUEST-ID: '.$requestId;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        $curlInfo = curl_getinfo($curl);

        $header = substr($response, 0, $curlInfo['header_size']);
        $body = substr($response, $curlInfo['header_size']);

        $this->debug && $this->logger('debug', "[{$requestId}] CURL_INFO", $curlInfo);
        $this->debug && $this->logger('debug', "[{$requestId}] RESPONSE_HEADER {$header}");
        $this->debug && $this->logger('debug', "[{$requestId}] RESPONSE_BODY {$body}");

        curl_close($curl);

        $context = array(
            'CURLINFO' => $curlInfo,
            'HEADER' => $header,
            'BODY' => $body,
        );

        if (empty($curlInfo['namelookup_time'])) {
            $this->logger('error', "[{$requestId}] NAME_LOOK_UP_TIMEOUT", $context);
        }

        if (empty($curlInfo['connect_time'])) {
            $this->logger('error', "[{$requestId}] API_CONNECT_TIMEOUT", $context);
            throw new \Exception("Connect api server timeout (url: {$url}).");
        }

        if (empty($curlInfo['starttransfer_time'])) {
            $this->logger('error', "[{$requestId}] API_TIMEOUT", $context);
            throw new \Exception("Request api server timeout (url:{$url}).");
        }

        if ($curlInfo['http_code'] >= 500) {
            $this->logger('error', "[{$requestId}] API_RESOPNSE_ERROR", $context);
            throw new \Exception("Api server internal error (url:{$url}).");
        }

        $result = json_decode($body, true);

        if (is_null($result)) {
            $this->logger('error', "[{$requestId}] RESPONSE_JSON_DECODE_ERROR", $context);
            throw new \Exception("Api result json decode error: (url:{$url}).");
        }

        if ($this->debug) {
            $this->logger('debug', "[{$requestId}] {$method} {$url}", array('params' => $params, 'headers' => $headers));
        }

        return $result;
    }

    protected function _makeAuthToken($url, $params)
    {
        $matched = preg_match('/:\/\/.*?(\/.*)$/', $url, $matches);

        if (!$matched) {
            throw new \RuntimeException('Make AuthToken Error.');
        }

        $text = $matches[1]."\n".json_encode($params)."\n".$this->secretKey;

        $hash = md5($text);

        return "{$this->accessKey}:{$hash}";
    }

    protected function logger($level, $message, array $data=array())
    {
        $log = date('Y-m-d H:i:s')." [{$level}] 8.0.0 ". $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $log, FILE_APPEND);
    }

    protected function getLoggerFile()
    {
        return \Topxia\Service\Common\ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/logs/upgrade.log';
    }
}

class EduSohoAppClient
{
    protected $userAgent = 'Open EduSoho App Client 1.0';

    protected $connectTimeout = 5;

    protected $timeout = 5;

    private $apiUrl = 'http://open.edusoho.com/app_api';

    private $debug = false;

    /**
     * @var string
     */
    private $accessKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * tmp dir path.
     *
     * @var string
     */
    private $tmpDir;

    public function __construct(array $options)
    {
        $this->accessKey = empty($options['accessKey']) ? 'Anonymous' : $options['accessKey'];
        $this->secretKey = empty($options['secretKey']) ? '' : $options['secretKey'];

        if (!empty($options['apiUrl'])) {
            $this->apiUrl = $options['apiUrl'];
        }

        $this->debug = empty($options['debug']) ? false : true;
        $this->tmpDir = empty($options['tmpDir']) ? sys_get_temp_dir() : $options['tmpDir'];
    }

    public function submitRunLog($log)
    {
        $args = array('log' => $log);

        return $this->callRemoteApi('POST', 'SubmitRunLog', $args);
    }

    protected function callRemoteApi($httpMethod, $action, array $args)
    {
        list($url, $httpParams) = $this->assembleCallRemoteApiUrlAndParams($action, $args);
        $result = $this->sendRequest($httpMethod, $url, $httpParams);

        return json_decode($result, true);
    }

    protected function assembleCallRemoteApiUrlAndParams($action, array $args)
    {
        $url = "{$this->apiUrl}?action={$action}";
        $edusoho = array(
            'edition' => 'opensource',
            'host' => $_SERVER['HTTP_HOST'],
            'version' => '8.0.0',
            'debug' => $this->debug ? '1' : '0',
        );
        $args['_edusoho'] = $edusoho;

        $httpParams = array();
        $httpParams['accessKey'] = $this->accessKey;
        $httpParams['args'] = $args;
        $httpParams['sign'] = hash_hmac('sha1', base64_encode(json_encode($args)), $this->secretKey);

        return array($url, $httpParams);
    }

    protected function sendRequest($method, $url, $params = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        if (strtoupper($method) == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
