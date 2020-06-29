<?php

namespace QiQiuYun\SDK\Service;

use Psr\Log\LoggerInterface;
use QiQiuYun\SDK\Auth;
use QiQiuYun\SDK\HttpClient\ClientInterface;

class S2B2CService extends BaseService
{
    //@todo 此处测试站，后改为正式站
    protected $defaultHost = 's2b2c-service.local.cg-dev.cn';

    public function __construct(Auth $auth, array $options = array(), LoggerInterface $logger = null, ClientInterface $client = null)
    {
        parent::__construct($auth, $options, $logger, $client);
        if (empty($this->host)) {
            $this->host = $this->defaultHost;
        }
    }

    /**
     * 上报支付成功的订单
     *
     * @param $order array biz_order
     * @param $orderItems array biz_order_item (每个Item需要增加源数据的Id信息, key为origin_product_id)
     *
     * @return
     */
    public function reportSuccessOrder($order, $orderItems)
    {
        $params = array(
            'merchantOrder' => $order,
            'merchantOrderItems' => $orderItems,
        );

        return $this->request('POST', '/order/report', $params);
    }

    /**
     * 上报退款成功的订单
     *
     * @param $order
     * @param $orderRefund
     * @param $orderRefundItems
     *
     * @return array
     */
    public function reportRefundOrder($order, $orderRefund, $orderRefundItems)
    {
        $params = array(
            'merchantOrder' => $order,
            'merchantOrderRefund' => $orderRefund,
            'merchantOrderRefundItems' => $orderRefundItems,
        );

        return $this->request('POST', '/order/report', $params);
    }

    /**
     * 获取渠道商自身信息
     *
     * @return array 用户信息
     */
    public function getMe()
    {
        $this->uri = '/merchants/me';

        return $this->sendRequest('getMe', array());
    }

    /**
     * 获取渠道商的代理商信息
     *
     * @return array 用户信息
     */
    public function getOwnSupplier()
    {
        $this->uri = '/merchants/own/supplier';

        return $this->sendRequest('getOwnSupplier', array());
    }

    /**
     * 搜索B端余额明细列表
     *
     * @param $conditions array('created_time_GTE', 'created_time_LTE', 'type')
     * @param $sorts
     * @param $start
     * @param $limit
     *
     * @return array array('items' => array(), 'count' => 0)
     */
    public function searchMerchantFlow($conditions, $sorts, $start, $limit)
    {
        $params = array(
            'conditions' => $conditions,
            'sorts' => $sorts,
            'start' => (int) $start,
            'limit' => (int) $limit,
        );

        return $this->request('GET', '/merchants/flows', $params);
    }

    /**
     * 搜索B端结算订单列表
     *
     * @param $conditions array('pay_time_GT', 'pay_time_LT', 'title_like', 'start_time', 'end_time')
     * @param $sorts
     * @param $start
     * @param $limit
     *
     * @return array array('items' => array(), 'count' => 0)
     */
    public function searchMerchantOrder($conditions, $sorts, $start, $limit)
    {
        $params = array(
            'conditions' => $conditions,
            'sorts' => $sorts,
            'start' => (int) $start,
            'limit' => (int) $limit,
        );

        return $this->request('GET', '/merchants/orders', $params);
    }

    /**
     * 搜索B端的已选商品
     *
     * @param $conditions
     * @param $sorts
     * @param $start
     * @param $limit
     *
     * @return array array('items' => array(), 'count' => 0)
     */
    public function searchDistribute($conditions, $sorts, $start, $limit)
    {
        $params = array(
            'conditions' => $conditions,
            'sorts' => $sorts,
            'start' => (int) $start,
            'limit' => (int) $limit,
        );

        return $this->request('GET', '/contents/search_distribute', $params);
    }

    // 获取余额明细详情接口路径
    private $flowDetailPath = '/merchants/flow_detail';

    // 获取订单详情接口路径
    private $orderDetailPath = '/merchants/order_detail';

    /**
     * 获取余额明细详情
     *
     * @return array (order,flow)
     */
    public function getFlowDetail($flowId)
    {
        $sendData = array('flowId' => $flowId);

        $this->uri = $this->flowDetailPath;

        return $this->sendRequest('getFlowDetail', $sendData);
    }

    /**
     * 获取订单列表详情
     *
     * @return array (order,merchant,flows)
     */
    public function getOrderDetail($orderSn)
    {
        $sendData = array('orderSn' => $orderSn);

        $this->uri = $this->orderDetailPath;

        return $this->sendRequest('getOrderDetail', $sendData);
    }

    // 批量获取分发课程状态接口路径
    private $distrabuteProductsPath = '/merchants/distrabute_products';

    /**
     * 获取订单列表详情
     *
     * @return array ()
     */
    public function findDistributeProducts($productIds)
    {
        $sendData = array(
            'productIds' => $productIds,
        );

        $this->uri = $this->distrabuteProductsPath;

        return $this->sendRequest('findDistrabuteProducts', $sendData);
    }

    // 修改采购课程价格的接口路径
    private $changeProductSellingPricePath = '/merchants/change/product/selling_price';

    public function changeProductSellingPrice($productId, $productType, $sellingPrice)
    {
        $sendData = array(
            'productId' => $productId,
            'productType' => $productType,
            'sellingPrice' => $sellingPrice,
        );
        $this->uri = $this->changeProductSellingPricePath;

        return $this->sendRequest('changeProductSellingPrice', $sendData, 'POST');
    }

    private $getProductUri = '/distribute/product';

    public function getDistributeProduct($productId)
    {
        $this->uri = $this->getProductUri ."/{$productId}";

        return $this->sendRequest('getDistributeProduct', array());
    }

    private $getDistributeContentUri = '/distribute/content';

    public function getDistributeContent($productDetailId)
    {
        $this->uri = $this->getDistributeContentUri ."/{$productDetailId}";

        return $this->sendRequest('getDistributeContent', array());
    }

    private $adoptDirtributeProductUri = '/distribute/product/{id}/adopt';

    /**
     * @return array ['status' => boolean,
     * array data =>  [
     *  array Product => [
     *      targetId => int,
     *      targetType => int
     *      ...array detail => [array ProductDetail ... ]
     *      ]
     *  ]
     * ]
     */
    public function adoptDirtributeProduct($productId)
    {
        $this->uri = str_replace('{id}', $productId, $this->adoptDirtributeProductUri);

        return $this->sendRequest('adoptDirtributeProduct', array(), 'POST');
    }

    public function changePurchaseStatusToRemoved($parentId, $productIds, $productType)
    {
        $this->uri = '/purchase/removed';

        $sendData = array(
            'parent_id' => $parentId,
            'product_ids' => $productIds,
            'product_type' => $productType,
        );

        return $this->sendRequest('changePurchaseStatusToRemoved', $sendData, 'POST');
    }

    public function upgrade($params)
    {
        $this->uri = '/upgrade';

        return $this->sendRequest('upgrade', $params, 'POST');
    }

    public function searchPurchaseProduct($conditions, $sorts, $start, $limit)
    {
        $this->uri = '/contents/search_purchase_product';

        $body = array(
            'conditions' => $conditions,
            'sorts' => $sorts,
            'start' => $start,
            'limit' => $limit,
        );

        return $this->sendRequest('searchPurchaseProduct', $body, 'GET');
    }

    // 调用接口路径
    protected $uri;

    // 资源播放列表接口路径
    private $hlsPlaylistPath = '/merchant_resource/hls/playlist';

    // 资源播放列表接口路径-json
    private $hlsPlaylistJsonPath = '/merchant_resource/hls/playlist/json';

    // 资源播放流接口路径
    private $hlsStreamPath = '/merchant_resource/hls/stream';

    private $hlsClefPlusPath = '/merchant_resource/hls/clef_plus';

    // 资源详情
    private $resourcePath = '/merchant_resource/detail';

    // 资源下载信息获取
    private $resourceDownloadPath = '/merchant_resource/download';

    // 资源信息的播放
    private $resourcePlayerPath = '/merchant_resource/player';

    private $purchaseProductPath = '/contents/purchase_product';

    /**
     * 获取商品资源播放列表m3u8
     *
     * @return array()
     */
    public function getProductHlsPlaylist($uri, $file, $params)
    {
        $sendData = $this->generateResourcesParams($uri, $file, $params);
        if (!empty($sendData['error'])) {
            return $sendData;
        }
        $this->uri = $this->hlsPlaylistPath;

        return $this->sendRequest('getProductHlsPlaylist', $sendData);
    }

    /**
     * 获取商品资源播放列表m3u8-json
     *
     * @return array()
     */
    public function getProductHlsPlaylistJson($uri, $file, $params)
    {
        $sendData = $this->generateResourcesParams($uri, $file, $params);
        if (!empty($sendData['error'])) {
            return $sendData;
        }
        $this->uri = $this->hlsPlaylistJsonPath;

        return $this->sendRequest('getProductHlsPlaylistJson', $sendData);
    }

    /**
     * 获取商品资源播放流数据
     *
     * @return array()
     */
    public function getProductHlsStream($uri, $file, $params)
    {
        $sendData = $this->generateResourcesParams($uri, $file, $params);
        if (!empty($sendData['error'])) {
            return $sendData;
        }
        $this->uri = $this->hlsStreamPath;

        return $this->sendRequest('getProductHlsStream', $sendData);
    }

    /**
     * 获取商品资源播放流数据
     *
     * @return array()
     */
    public function getProductHlsClefPlus($uri, $file, $params)
    {
        $sendData = $this->generateResourcesParams($uri, $file, $params);
        if (!empty($sendData['error'])) {
            return $sendData;
        }
        $this->uri = $this->hlsClefPlusPath;

        return $this->sendRequest('getProductHlsClefPlus', $sendData);
    }

    /**
     * 获取商品资源的信息
     *
     * @return array()
     */
    public function getProductResource($uri, $file, $params)
    {
        $sendData = $this->generateResourcesParams($uri, $file, $params);
        if (!empty($sendData['error'])) {
            return $sendData;
        }
        $sendData['params']['resourceNo'] = $sendData['resourceNo'];
        $this->uri = $this->resourcePath;

        return $this->sendRequest('getProductResource', $sendData);
    }

    /**
     * 获取商品资源的播放信息
     *
     * @return array()
     */
    public function getProductResourcePlayer($uri, $file, $params)
    {
        $sendData = $this->generateResourcesParams($uri, $file, $params);
        if (!empty($sendData['error'])) {
            return $sendData;
        }
        $sendData['params']['resourceNo'] = $sendData['resourceNo'];
        $this->uri = $this->resourcePlayerPath;

        return $this->sendRequest('getProductResourcePlayer', $sendData);
    }

    /**
     * 获取商品资源的下载信息
     *
     * @return array()
     */
    public function getProductResDownload($uri, $file, $params)
    {
        $sendData = $this->generateResourcesParams($uri, $file, $params);
        if (!empty($sendData['error'])) {
            return $sendData;
        }
        $sendData['params']['resourceNo'] = $sendData['resourceNo'];
        $this->uri = $this->resourceDownloadPath;

        return $this->sendRequest('getProductResDownload', $sendData);
    }

    public function purchaseProducts($purchaseProducts, $purchaseRecord)
    {
        $this->uri = $this->purchaseProductPath;
        $body = array(
            'products' => $purchaseProducts,
            'record' => $purchaseRecord,
        );

        return $this->sendRequest('purchaseProducts', $body, 'POST');
    }

    /**
     * 生成调用商品资源类接口的基础参数
     *
     * @param string $uri    访问的路由
     * @param array  $file   资源文件内容
     * @param array  $params 额外参数数组
     *
     * @return array()
     */
    protected function generateResourcesParams($uri, $file, $params)
    {
        $this->logger->info('调用接口uri: '.$uri.' start');
        if (empty($uri) || empty($file) || !isset($file['sourceTargetId'])) {
            $this->logger->error('generateResourcesParams error: params missing', array('uri' => $uri, 'file' => $file, 'params' => $params));

            return $this->createErrorResult();
        }
        try {
            $sendData = array(
                'resourceNo' => $file['globalId'],
                'uri' => $uri,
                'productType' => 'course',
                'distributeId' => $file['sourceTargetId'],
                'params' => empty($params) ? array() : $params,
            );
        } catch (\Exception $e) {
            $this->logger->error('generateResourcesParams failed: '.$e->getMessage(), array('uri' => $uri, 'file' => $file, 'params' => $params));

            return $this->createErrorResult();
        }

        return $sendData;
    }

    private function sendRequest($methodName, $data, $requestMethod = 'GET')
    {
        try {
            $this->logger->info('try '.$methodName.': ', array('DATA' => $data));
            $result = $this->request($requestMethod, $this->uri, $data);
            $this->logger->info($methodName.' SUCCEED', array($result));
        } catch (\Exception $e) {
            $this->logger->error($methodName.' error: '.$e->getMessage(), array('DATA' => $data));

            return $this->createErrorResult($e->getMessage());
        }

        return $result;
    }

    protected function createErrorResult($message = 'unexpected error')
    {
        return array('error' => $message);
    }
}
