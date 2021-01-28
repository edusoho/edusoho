<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class WxpayBillCommand extends BaseCommand
{
    private $host = '';

    protected function configure()
    {
        $this->setName('util:upgrade-orders-by-wxpaybill')
            ->addArgument('host', InputArgument::REQUIRED, '域名')
            ->addArgument('billdate', InputArgument::REQUIRED, '账单日期')
            ->setDescription('用于命令行下载对账单，模拟回调');
    }

    protected function getPayLogger()
    {
        $logger = new Logger('WxpayBillCommand');
        $logger->pushHandler(new StreamHandler($this->getServiceKernel()->getParameter('kernel.logs_dir').'/wxpayBillCommand.log', Logger::DEBUG));

        return $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $this->host = $input->getArgument('host');
        $billdate = $input->getArgument('billdate');
        $this->getWxpayBill($billdate);
    }

    private function getWxpayBill($billdate)
    {
        $payment = 'wxpay';
        $settings = $this->getSettingService()->get('payment');
        $options = array(
                'appid' => $settings["{$payment}_appid"],
                'account' => $settings["{$payment}_account"],
                'key' => $settings["{$payment}_key"],
                'secret' => $settings["{$payment}_secret"],
            );
        $params = array();
        $params['appid'] = $options['appid'];
        $params['mch_id'] = $options['account'];
        $params['nonce_str'] = $this->getNonceStr();
        $params['bill_date'] = $billdate;
        $params['bill_type'] = 'ALL';
        $params['key'] = $options['key'];
        $params['sign'] = $this->signParams($params);
        $xml = $this->toXml($params);
        $response = $this->postData($xml);
        if (substr($response, 0, 5) == '<xml>') {
            $this->getPayLogger()->addError($response);

            return;
        }
        $data = explode("\r", $response);
        $length = count($data);
        $j = 1;
        for ($i = 1; $i < $length - 3; ++$i) {
            $lineData = explode(',', $data[$i]);
            $orderStatus = $lineData[9];
            $out_trade_no = $lineData[6];
            if ($orderStatus === '`SUCCESS') {
                $out_trade_no = substr($out_trade_no, 1);
                if (strlen($out_trade_no) >= 25) {
                    $sn = substr($out_trade_no, 0, strlen($out_trade_no) - 5);
                    $this->getPayLogger()->addInfo('out_trade_no:'.$out_trade_no.',转化之后sn:'.$sn);
                } else {
                    $sn = $out_trade_no;
                }
                $this->getPayLogger()->addInfo('根据Sn:'.$sn.',查询订单');
                $order = $this->getOrderService()->getOrderBySn($sn);
                if ($order['status'] == 'paid') {
                    $this->getPayLogger()->addInfo('sn:'.$order['sn'].'，状态一致，不用处理');
                    continue;
                }

                $this->getPayLogger()->addInfo('sn:'.$order['sn'].'，状态不一致，开始处理,'.$j);
                //if($j == 1){
                $this->processOrder($order, $out_trade_no);
                //}
                ++$j;
            }
        }
    }

    private function getNonceStr($length = 32)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';

        for ($i = 0; $i < $length; ++$i) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    private function signParams($params)
    {
        ksort($params);
        $sign = '';
        $buff = '';
        foreach ($params as $k => $v) {
            if ($k != 'sign' && $v != '' && !is_array($v)) {
                $buff .= $k.'='.$v.'&';
            }
        }
        $buff = trim($buff, '&');

        $buff = $buff.'&key='.$params['key'];
        $buff = md5($buff);
        $result = strtoupper($buff);

        return $result;
    }

    protected function processOrder($order, $out_trade_no)
    {
        if ($order['status'] == 'paid') {
            echo $order['status'];

            return;
        }
        $gotoParameters['name'] = 'wxpay';
        $gotoParameters['sn'] = $order['sn'];
        $gotoParameters['out_trade_no'] = $out_trade_no;
        $this->payReturn('wxpay', $gotoParameters);
    }

    private function payReturn($name, $data)
    {
        $host = $this->host;
        $this->getPayLogger()->addInfo('payReturn');

        $url = "http://{$host}/pay/center/pay/{$name}/return?";
        $url = $url.http_build_query($data);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
    }

    private function toXml($array, $xml = false)
    {
        if (!is_array($array)
            || count($array) <= 0
        ) {
            throw new \Exception('数组数据异常！');
        }

        $xml = '<xml>';
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= '<'.$key.'>'.$val.'</'.$key.'>';
            } else {
                $xml .= '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
            }
        }
        $xml .= '</xml>';

        return $xml;
    }

    private function postData($xml)
    {
        $downloadBillUrl = 'https://api.mch.weixin.qq.com/pay/downloadbill';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $downloadBillUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        //运行curl
        $data = curl_exec($ch);
        // $str = fgets($data);
        curl_close($ch);

        return $data;
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
