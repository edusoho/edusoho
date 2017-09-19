<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;

class LianlianpayController extends BaseController
{
    public function payAction($trade)
    {
        $user = $this->getUser();
        $trade['platform_type'] = $this->isMobileClient() ? 'Wap' : 'Web';
        $trade['attach']['user_created_time'] = $user['createdTime'];
        $trade['attach']['identify_user_id'] = $this->getIdentify().'_'.$user['id'];
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'lianlianpay'), true);
        $trade['return_url'] = $this->generateUrl('cashier_pay_return', array('payment' => 'lianlianpay'), true);
        $trade['show_url'] = $this->generateUrl('cashier_pay_success', array('trade_sn' => '1234'), true);

        $result = $this->getPayService()->createTrade($trade);

        if ($result['status'] == 'paid') {
            return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $result['trade_sn'])));
        }

        return $this->redirect($result['platform_created_result']['url']);
    }

    public function notifyAction(Request $request, $payment)
    {
        $returnArray = json_decode(file_get_contents('php://input'), true);
        $result = $this->getPayService()->notifyPaid($payment, $returnArray);

        return $this->createJsonResponse($result);
    }

    public function returnAction(Request $request, $payment)
    {
        $data = $request->request->all();
        $this->getPayService()->notifyPaid($payment, $data);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['no_order']), true));
    }

    protected function getIdentify()
    {
        $identify = $this->getSettingService()->get('llpay_identify');
        if (empty($identify)) {
            $identify = substr(md5(uniqid()), 0, 12);
            $this->getSettingService()->set('llpay_identify', $identify);
        }

        return $identify;
    }

    protected function isMobileClient()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }

        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        }

        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp',
                'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
                'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi',
                'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile',
            );

            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match('/('.implode('|', $clientkeywords).')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }

        //协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
                && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false
                    || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))
                )) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
