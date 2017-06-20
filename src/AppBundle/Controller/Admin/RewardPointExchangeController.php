<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\FileToolkit;
use Symfony\Component\HttpFoundation\Response;

class RewardPointExchangeController extends BaseController
{
    public function indexAction(Request $request)
    {
        if (!$this->getAccountService()->hasRewardPointPermission()) {
            return $this->createMessageResponse('error', '积分没有开启,请联系管理员！');
        }
        $fields = $request->query->all();
        $conditions = array(
            'keywordType' => '',
            'keywordStatus' => '',
            'keyword' => '',
        );

        $conditions = array_merge($conditions, $fields);
        $paginator = new Paginator(
            $request,
            $this->getRewardPointProductOrderService()->countProductOrders($conditions),
            20
        );
        $orders = $this->getRewardPointProductOrderService()->searchProductOrders(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $productIds = ArrayToolkit::column($orders, 'productId');
        $products = $this->getRewardPointProductService()->findProductsByIds($productIds);

        $userIds = ArrayToolkit::column($orders, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'admin/reward-point-mall/exchange/index.html.twig',
            array(
                'orders' => $orders,
                'paginator' => $paginator,
                'products' => ArrayToolkit::index($products, 'id'),
                'users' => ArrayToolkit::index($users, 'id'),
            )
        );
    }

    public function deliverAction(Request $request, $id)
    {
        $order = $this->getRewardPointProductOrderService()->getProductOrder($id);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $order = $this->getRewardPointProductOrderService()->deliverProduct($id, $fields);

            $product = $this->getRewardPointProductService()->getProduct($order['productId']);

            $user = $this->getUserService()->getUser($order['userId']);

            return $this->render('admin/reward-point-mall/exchange/list-tr.html.twig',
                array(
                    'order' => $order,
                    'product' => $product,
                    'user' => $user,
                )
            );
        }

        return $this->render('admin/reward-point-mall/exchange/modal.html.twig',
            array(
                'order' => $order,
            )
        );
    }

    public function updateMessageAction(Request $request, $id)
    {
        $order = $this->getRewardPointProductOrderService()->getProductOrder($id);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $order = $this->getRewardPointProductOrderService()->updateProductOrder($id, $fields);

            $product = $this->getRewardPointProductService()->getProduct($order['productId']);

            $user = $this->getUserService()->getUser($order['userId']);

            return $this->render('admin/reward-point-mall/exchange/list-tr.html.twig',
                array(
                    'order' => $order,
                    'product' => $product,
                    'user' => $user,
                )
            );
        }

        return $this->render(
            'admin/reward-point-mall/exchange/modal.html.twig',
            array(
                'order' => $order,
            )
        );
    }

    public function exportCsvAction(Request $request)
    {
        $start = $request->query->get('start', 0);
        $magic = $this->setting('magic');
        $limit = $magic['export_limit'];

        $conditions = $this->buildExportCondition($request);
        $keywordStatus = array(
            'created' => '未发货',
            'finished' => '已发货',
        );

        $orderCount = $this->getRewardPointProductOrderService()->countProductOrders($conditions);
        if ($orderCount > 1000) {
            return $this->createMessageResponse('error', '数据超过1000条，请调整查询范围后再试。');
        }
        $orders = $this->getRewardPointProductOrderService()->searchProductOrders($conditions, array('createdTime' => 'DESC'), $start, $limit);
        $userIds = ArrayToolkit::column($orders, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $productIds = ArrayToolkit::column($orders, 'productId');
        $products = $this->getRewardPointProductService()->findProductsByIds($productIds);
        $products = ArrayToolkit::index($products, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $str = '商品名称,收货人,收获地址,联系电话,邮箱,兑换者用户名,姓名,兑换时间,发货';
        $str .= "\r\n";

        $results = array();
        $results = $this->generateExportData($orders, $users, $products, $keywordStatus, $profiles, $results);

        $loop = $request->query->get('loop', 0);
        ++$loop;

        $enableRedirect = $loop * $limit < $orderCount;
        $readTempDate = $start;
        $file = $request->query->get('fileName', $this->genereateExportCsvFileName());

        if ($enableRedirect) {
            $content = implode("\r\n", $results);
            file_put_contents($file, $content."\r\n", FILE_APPEND);

            return $this->redirect(
                $this->generateUrl(
                    'admin_reward_point_product_order_export_csv',
                    array('loop' => $loop, 'start' => $loop * $limit, 'fileName' => $file)
                )
            );
        } elseif ($readTempDate) {
            $str .= file_get_contents($file);
            FileToolkit::remove($file);
        }

        $str .= implode("\r\n", $results);
        $str = chr(239).chr(187).chr(191).$str;
        $filename = sprintf('%s-exchange-record(%s).csv', 'product', date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    private function buildExportCondition($request)
    {
        $conditions = $request->query->all();
        if (!empty($conditions['startDate']) && !empty($conditions['endDate'])) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
            $conditions['endDate'] = strtotime($conditions['endDate']);
        }

        return $conditions;
    }

    private function genereateExportCsvFileName()
    {
        $rootPath = $this->getParameter('topxia.upload.private_directory');
        $user = $this->getUser();

        return $rootPath.'/export_content'.$user['id'].time().'.txt';
    }

    private function generateExportData($orders, $users, $products, $keywordStatus, $profiles, $results)
    {
        foreach ($orders as $key => $order) {
            $member = '';
            $member .= $products[$order['productId']]['title'].',';
            $member .= $order['consignee'].',';
            $member .= $order['address'].',';
            $member .= $order['telephone'].',';
            $member .= $order['email'].',';
            $member .= $users[$order['userId']]['nickname'].',';
            $member .= $profiles[$order['userId']]['truename'] ? $profiles[$order['userId']]['truename'].',' : '-'.',';
            $member .= date('Y-n-d H:i:s', $order['createdTime']).',';
            if ($order['status'] == 'created') {
                $member .= $keywordStatus['created'];
            } else {
                $member .= $keywordStatus['finished'];
            }

            $results[] = $member;
        }

        return $results;
    }

    /**
     * @return RewardPointProductService
     */
    protected function getRewardPointProductService()
    {
        return $this->createService('RewardPoint:ProductService');
    }

    /**
     * @return RewardPointProductOrderService
     */
    protected function getRewardPointProductOrderService()
    {
        return $this->createService('RewardPoint:ProductOrderService');
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }
}
