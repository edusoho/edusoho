<?php

namespace MarketingMallBundle\Client;

use AppBundle\Common\ArrayToolkit;
use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification;
use Firebase\JWT\JWT;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class MarketingMallApi
{
    private static $client;

    private static $logger;

    private static $headers = [];

    private static $timestamp = null;

    private static $accessKey = '';

    private static $secretKey = '';

    public function __construct($storage)
    {
        $mallUrl = ServiceKernel::instance()->getParameter('marketing_mall_url');
        $setting = $this->getSettingService()->get('marketing_mall', []);
        self::$accessKey = $setting['access_key'] ?? '';
        self::$secretKey = $setting['secret_key'] ?? '';
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Mall-Auth-Token: ' . $this->makeToken();
        self::$headers = $headers;
        self::$timestamp = time();
        $config = [
            'access_key' => $storage['cloud_access_key'] ?? '',
            'secret_key' => $storage['cloud_secret_key'] ?? '',
            'endpoint' => empty($storage['mall_private_server']) ? $mallUrl : rtrim($storage['mall_private_server'], '/'),
        ];
        $logger = self::getLogger();
        $spec = new JsonHmacSpecification('sha1');
        self::$client = new RestApiClient($config, $spec, null, $logger);
    }

    public function init($params)
    {
        try {
            $params = ArrayToolkit::parts($params, ['token', 'url', 'code']);
            $result = $this->post('/api-admin/es-data/init', $params);
            if (empty($result['accessKey'])) {
                throw new \InvalidArgumentException('接口请求错误!');
            }

            return $result;
        } catch (\RuntimeException $e) {
            $this->getLogger()->error('market-mall-init', ['商城初始化错误' . $e->getMessage()]);
            throw new \InvalidArgumentException('接口请求错误!');
        }
    }

    public function updateGoodsContent($params)
    {
        try {
            $params = ArrayToolkit::parts($params, ['type', 'body']);
            $this->post('/api-admin/goods/update_goods_content', $params);
        } catch (\RuntimeException $e) {
            $this->getLogger()->error('更新商品详情错误' . $e->getMessage(), ['params' => $params]);
            throw new \InvalidArgumentException('接口请求错误!');
        }
    }

    public function checkGoodsIsPublishByCode($params)
    {
        return $this->get('/api-school/goods/getGoodsPublishStatus?goodsCode=' . $params[0]);
    }

    public function deleteGoodsByCode($params)
    {
        return $this->post('/api-school/goods/esDeleteGoods', ['code' => $params[0]]);
    }

//    例子  token头直接设置了参数code也直接加了
//    public function demo($params){
//        try {
//            $params = ArrayToolkit::parts($params, ['token', 'url', 'code']);
//            $result = $this->post('/api-admin/es-data/init', $params);
//        } catch (\RuntimeException $e) {
//            $this->getLogger()->error('market-mall-init',  '商城初始化错误'.$e->getMessage());
//            throw new \InvalidArgumentException('接口请求错误!');
//        }
//    }
//
    private function get($uri, array $params = [])
    {
        $params['code'] = self::$accessKey;

        return self::$client->get($uri, $params, self::$headers);
    }

    private function post($uri, array $params = [])
    {
        return self::$client->post($uri, $params, self::$headers);
    }

    private function makeToken()
    {
        return self::$accessKey . ':' . JWT::encode(['exp' => time() + 1000 * 3600 * 24, 'access_key' => self::$accessKey], self::$secretKey);
    }

    /**
     * 仅供单元测试使用，正常业务严禁使用
     *
     * @param $client
     */
    public function setCloudApi($client)
    {
        self::$client = $client;
    }

    private function getLogger()
    {
        if (self::$logger) {
            return self::$logger;
        }
        $logger = new Logger('marketing-mall');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir') . '/marketing-mall.log', Logger::DEBUG));

        self::$logger = $logger;

        return $logger;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
