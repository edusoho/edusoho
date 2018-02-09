<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\TimeMachine;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Distributor\Util\DistributorJobStatus;
use Biz\Distributor\Job\DistributorSyncJob;
use AppBundle\Common\ReflectionUtils;
use Codeages\Weblib\Auth\SignatureTokenAlgo;

class MockController extends BaseController
{
    public function indexAction()
    {
        $this->validate();
        $tokenExpireDateStr = TimeMachine::expressionToStr('+2 day');

        return $this->render('admin/mock/index.html.twig', array(
            'couponExpireDateStr' => 1,
            'tokenExpireDateStr' => $tokenExpireDateStr,
        ));
    }

    public function mockDistributorTokenAction(Request $request)
    {
        $this->validate();

        $data = array(
            'merchant_id' => '123',
            'agency_id' => '22221',
            'coupon_price' => $request->request->get('couponPrice'),
            'coupon_expiry_day' => $request->request->get('couponExpiryDay'),
        );

        $tokenExpireDateNum = strtotime($request->request->get('tokenExpireDateStr'));
        $token = $this->getDistributorUserService()->encodeToken($data, $tokenExpireDateNum);

        return $this->createJsonResponse(array(
            'token' => $token,
        ));
    }

    public function getPostDistributorDataAction(Request $request)
    {
        $this->validate();

        $type = $request->request->get('type');
        $service = $this->getDistributorService($type);
        $jobData = $service->findJobData();

        return $this->createJsonResponse($jobData);
    }

    public function postDistributorDataAction(Request $request)
    {
        $this->validate();

        $type = $request->request->get('type');
        $service = $this->getDistributorService($type);
        $drpService = $service->getDrpService();

        if (!empty($drpService)) {
            $job = new DistributorSyncJob(array(), $this->getBiz());
            $result = ReflectionUtils::invokeMethod($job, 'sendData', array($drpService, $service));
            if (DistributorJobStatus::FINISHED == $result['status']) {
                return $this->createJsonResponse(array('result' => 'true'));
            } else {
                return $this->createJsonResponse(array('result' => $result['result']));
            }
        }
    }

    public function postMarketingDataAction(Request $request)
    {
        $this->validate();

        $url = $request->request->get('url');
        $bodyStr = $request->request->get('body');

        $result = $this->post($url, $bodyStr, $this->generateToken($url, $bodyStr));

        return $this->createJsonResponse(array('result' => $result));
    }

    protected function getDistributorUserService()
    {
        return $this->createService('Distributor:DistributorUserService');
    }

    protected function getDistributorService($type)
    {
        return $this->createService("Distributor:Distributor{$type}Service");
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    private function validate()
    {
        $validHosts = array('local', 'try6.edusoho.cn', 'dev', 'esdev.com', 'localhost');
        $host = $_SERVER['HTTP_HOST'];
        if (!in_array($host, $validHosts)) {
            throw new AccessDeniedException($host.'不允许使用此功能！！！');
        }

        $storage = $this->getSettingService()->get('storage', array());
        if (empty($storage['cloud_access_key'])) {
            throw new AccessDeniedException('未设置教育云授权码！！！');
        }

        if (!$this->getCurrentUser()->isSuperAdmin()) {
            throw new AccessDeniedException();
        }
    }

    private function generateToken($url, $body)
    {
        $strategy = new SignatureTokenAlgo();

        $deadline = TimeMachine::time() + 600;
        $once = 'test_once';

        $signText = "{$url}\n{$body}";

        $storageSetting = $this->getSettingService()->get('storage', array());
        $cloudAccessKey = $storageSetting['cloud_access_key'];

        $signatureText = $strategy->signature(
            "{$once}\n{$deadline}\n{$signText}",
            $storageSetting['cloud_secret_key']
        );

        return "{$cloudAccessKey}:{$deadline}:{$once}:{$signatureText}";
    }

    private function post($url, $bodyStr, $token)
    {
        $url = 'http://127.0.0.1'.$url;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodyStr);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Signature '.$token,
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
