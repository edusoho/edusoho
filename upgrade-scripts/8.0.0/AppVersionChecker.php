<?php
use Topxia\Service\Common\ServiceKernel;

class AppVersionChecker extends AbstractMigrate
{
    public function update($page)
    {
        $crm = $this->getAppService()->getAppByCode('Crm');

        if (!empty($crm)) {
            $storage = $this->getSettingService()->get('storage', array());

            $openApi = new AppVersionCheckerEduSohoAppClient(array(
                'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
            ));

            $crmResult = $openApi->getAppStatusByCode('Crm');

            if (!empty($crmResult['status']) && $crmResult['status'] == 'expired') {
                $this->removeCrmPlugin();
            }
        }

        $apps = $this->getApps();
        $localApps = $this->getAppService()->findApps(0, 1000);

        $errors = array();
        $mainApp = array();
        foreach ($localApps as $key => $localApp) {
            if (!empty($apps[strtolower($localApp['code'])]) && version_compare($localApp['version'], $apps[strtolower($localApp['code'])], '<')) {
                $errors[] = $localApp['name'];
            }
            if ($localApp['code'] == 'MAIN') {
                $mainApp = $localApp;
            }
        }
        if ($localApp && isset($mainApp['version'])) {
            if (version_compare('8.0.0', $mainApp['version'], '<=')) {
                throw new Exception(sprintf('当前版本(%s)依赖不匹配，或页面请求已过期，请刷新后重试', $mainApp['version']));
            }
        }
        if (!empty($errors)) {
            $names = implode('、', $errors);
            throw new Exception("当前以下插件{$names}版本过低，请先升级以上插件", 1);
        }
    }

    protected function removeCrmPlugin()
    {
        $this->getConnection()->exec("DELETE FROM cloud_app WHERE code = 'Crm'");

        $pluginConfig = ServiceKernel::instance()->getParameter('kernel.root_dir') . '/../app/config/plugin.php';

        $config = require $pluginConfig;

        if (isset($config['installed_plugins']['Crm'])) {
            $installedPlugins = $config['installed_plugins'];
            unset($installedPlugins['Crm']);
            $config['installed_plugins'] = $installedPlugins;
        }

        $config = is_array($config) ? $config : array();

        $content = "<?php \n return " . var_export($config, true) . ";";
        $saved = file_put_contents($pluginConfig, $content);

        $file = ServiceKernel::instance()->getParameter('kernel.root_dir') . '/../app/config/routing_plugins.yml';
        $pluginRoutes = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));
        unset($pluginRoutes['_plugin_Crm_admin']);
        $pluginRouteString = \Symfony\Component\Yaml\Yaml::dump($pluginRoutes);
        file_put_contents($file, $pluginRouteString);
    }

    protected function getApps()
    {
        return array(
            'vip' => '1.6.5',
            'coupon' => '2.1.5',
            'questionplus' => '1.2.1',
            'gracefultheme' => '1.4.23',
            'userimporter' => '2.1.5',
            'homework' => '1.5.5',
            'chargecoin' => '1.2.5',
            'moneycard' => '2.0.4',
            'anywhereserver' => '1.0.4',
            'desire' => '1.1.6',
            'discount' => '1.1.7',
            'language' => '1.0.8',
            'turing' => '1.1.11',
            'fileshare' => '1.0.4',
            'groupsell' => '1.0.2',
            'exam' => '1.2.3',
            'lighttheme' => '2.2.0',
            'crm' => '1.0.1',
            'favoritereward' => '1.0.2',
            'rainbowtree' => '1.0.0',
            'zero' => '1.0.0',
        );
    }

    protected function getSettingService()
    {
        if ($this->isX8()) {
            return ServiceKernel::instance()->createService('System:SettingService');
        }
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getAppService()
    {
        if ($this->isX8()) {
            return ServiceKernel::instance()->createService('CloudPlatform:AppService');
        }
        return ServiceKernel::instance()->createService('CloudPlatform.AppService');
    }
}

class AppVersionCheckerEduSohoAppClient
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

    public function getAppStatusByCode($code)
    {
        $args = array('appCode' => $code);

        return $this->callRemoteApi('GET', 'GetMyAppStatus', $args);
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
        }
        else {
            if (!empty($params)) {
                $url = $url . (strpos($url, '?') ? '&' : '?') . http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
