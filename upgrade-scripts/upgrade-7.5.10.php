<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Component\Payment\Payment;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        try {
            $this->checkOrders();
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function checkOrders()
    {
        $orders = $this->getOrders();
        foreach($orders as $order) {
            $this->processOrder($order);
        }
    }

    protected function getOrders()
    {
        $sql = "SELECT * FROM `orders` WHERE createdTime > ".strtotime("2017-03-19 16:00:00");
        return $this->getConnection()->fetchAll($sql, array());
    }
    
    protected function processOrder($order)
    {
        if(in_array($order['payment'], array('wxpay'))) {
            if($order['status'] == 'paid') {
                return;
            }

            $options = $this->getPaymentOptions($order['payment']);

            $payment = Payment::createTradeQueryRequest($order['payment'], $options);
            $payment->setParams($order);

            $result = $payment->tradeQuery();

            $this->getOrderService()->updateOrder($order['id'], array('status'=>'created'));

            $this->postData($order['payment'], $result);
        }
    }

    private function postData($name, $data)
    {
        global $request;
        $url = $request->getSchemeAndHttpHost()."/pay/center/pay/{$name}/notify";

        $header = array();
        $header[] = "Content-type: text/xml"; 

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);

        curl_close($ch);
    }

    protected function getPaymentOptions($payment)
    {
        $settings = $this->getSettingService()->get('payment');

        $options = array(
            'key'    => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
        );

        return $options;
    }

    protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}
