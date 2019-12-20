<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Common\TimeMachine;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Distributor\Service\Impl\DistributorCourseOrderServiceImpl;
use Biz\System\Service\SettingService;
use Biz\System\SettingException;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Distributor\Util\DistributorJobStatus;
use Biz\Distributor\Job\DistributorSyncJob;
use AppBundle\Common\ReflectionUtils;
use Codeages\Weblib\Auth\SignatureTokenAlgo;
use Biz\Distributor\Util\DistributorUtil;
use Topxia\MobileBundleV2\Controller\MobileBaseController;

class MockController extends BaseController
{
    public function indexAction()
    {
        $this->validate();
        $tokenExpireDateStr = TimeMachine::expressionToStr('+2 day');

        return $this->render('admin-v2/developer/mock/index.html.twig', array(
            'couponExpireDateStr' => 1,
            'tokenExpireDateStr' => $tokenExpireDateStr,
            'typeSamples' => $this->getTypeSamples(),
            'comment' => $this->getApiBaseComment(),
        ));
    }

    public function mockDistributorTokenAction(Request $request, $type)
    {
        $this->validate();

        $params = $request->request->all();
        $token = DistributorUtil::generateTokenByType($this->getBiz(), $type, $params);

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

    public function postDataWithVersion3Action(Request $request)
    {
        $this->validate();

        $params = $request->request->get('data');

        if ('generateToken' == $params['apiUrl']) {
            $apiUserId = empty($params['apiUserId']) ? null : $params['apiUserId'];

            if (!empty($apiUserId)) {
                $user = $this->getUserService()->getUser($apiUserId);
                if (empty($user)) {
                    $this->createNewException(UserException::NOTFOUND_USER());
                }
                $token = $this->getUserService()->makeToken(
                    MobileBaseController::TOKEN_TYPE,
                    $user['id'],
                    time() + 3600 * 24 * 30
                );
            }
            $result = array('X-Auth-Token' => $token);
        } else {
            $result = $this->sendApiVersion3($params);
        }

        return $this->createJsonResponse(array('result' => $result));
    }

    public function downloadTokenAction()
    {
        $this->validate();

        $path = $this->getMockedTokenPath();

        return $this->createJsonResponse(array('result' => file_get_contents($path)));
    }

    /**
     * @return DistributorCourseOrderServiceImpl
     */
    protected function getDistributorCourseOrderService()
    {
        return $this->createService('Distributor:DistributorCourseOrderService');
    }

    protected function getDistributorService($type)
    {
        return $this->createService("Distributor:Distributor{$type}Service");
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    private function validate()
    {
        $validHosts = array(
            'local',
            'dev',
            'esdev.com',
            'localhost',
            'www.edusoho-test1.com',
        );
        $host = $_SERVER['HTTP_HOST'];
        if (!in_array($host, $validHosts) && false === strpos($host, '.edusoho.cn')) {
            throw new AccessDeniedException($host.'不允许使用此功能！！！');
        }

        $storage = $this->getSettingService()->get('storage', array());
        if (empty($storage['cloud_access_key'])) {
            $this->createNewException(SettingException::NOT_SET_CLOUD_ACCESS_KEY());
        }

        if (!$this->getCurrentUser()->isSuperAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
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

    private function sendApiVersion3($params = array(), $conditions = array())
    {
        $apiUrl = $params['apiUrl'];
        $apiMethod = $params['apiMethod'];
        $apiAuthorized = $params['apiAuthorized'];
        $apiUserId = empty($params['apiUserId']) ? null : $params['apiUserId'];

        unset($params['apiUrl']);
        unset($params['apiMethod']);
        unset($params['apiAuthorized']);
        unset($params['apiUserId']);

        if (!empty($apiUserId)) {
            $user = $this->getUserService()->getUser($apiUserId);
            if (empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
            $token = $this->getUserService()->makeToken(
                MobileBaseController::TOKEN_TYPE,
                $user['id'],
                time() + 3600 * 24 * 30
            );
        }

        $url = $_SERVER['HTTP_ORIGIN'].$apiUrl;

        $conditions['userAgent'] = isset($conditions['userAgent']) ? $conditions['userAgent'] : '';
        $conditions['connectTimeout'] = isset($conditions['connectTimeout']) ? $conditions['connectTimeout'] : 10;
        $conditions['timeout'] = isset($conditions['timeout']) ? $conditions['timeout'] : 10;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $conditions['userAgent']);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $conditions['connectTimeout']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $conditions['timeout']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        $headers = array('Accept: application/vnd.edusoho.v2+json');
        if ('true' == $apiAuthorized) {
            $token = $this->generateToken($apiUrl, '');
            $headers[] = 'Authorization: Signature '.$token;
            $this->saveMockedToken($token);
        } elseif (!empty($token)) {
            $headers[] = 'X-Auth-Token: '.$token;
            $headers[] = 'User-Agent: CERN-LineMode/2.15 libwww/2.17b3';
        }

        if ('POST' == $apiMethod) {
            curl_setopt($curl, CURLOPT_POST, 1);
            //TODO
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } elseif ('PUT' == $apiMethod) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('DELETE' == $apiMethod) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('PATCH' == $apiMethod) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
            $headers[] = 'Content-Type: application/json-patch+json';
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);

        $header = substr($response, 0, $curlinfo['header_size']);
        $body = substr($response, $curlinfo['header_size']);

        curl_close($curl);

        if (empty($curlinfo['namelookup_time'])) {
            return array();
        }

        if (isset($conditions['contentType']) && 'plain' == $conditions['contentType']) {
            return $body;
        }
        $result = json_decode($body, true);

        if (empty($result)) {
            $result = array('detailedMsg' => $body);
        }

        return $result;
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

    private function getTypeSamples()
    {
        $biz = $this->getBiz();
        $dir = $biz['root_directory'].'app/Resources/views/admin-v2/developer/mock/sample-data/';
        $fileNames = scandir($dir);
        $typeSamples = array();
        foreach ($fileNames as $fileName) {
            if (strpos($fileName, '.md') && !strpos($fileName, '_doc')) {
                $typeNamesSeg = explode('.md', $fileName);
                $typeName = $typeNamesSeg[0];
                $fileContent = file_get_contents($dir.$fileName);
                $docContent = file_get_contents($dir.$typeName.'_doc.md');
                $keyValue = array();
                $keyValue['key'] = $typeName;
                $keyValue['value'] = $fileContent;
                $keyValue['doc'] = $docContent;
                $keyValue['apiInfo'] = $this->getApiInfo($docContent);
                $typeSamples[] = $keyValue;
            }
        }

        return $typeSamples;
    }

    private function getApiBaseComment()
    {
        $biz = $this->getBiz();
        $file = $biz['root_directory'].'app/Resources/views/admin-v2/developer/mock/sample-data-comment/comment.md';

        return file_get_contents($file);
    }

    private function getApiInfo($docContent)
    {
        preg_match('/api-version: (.*?)\n/s', $docContent, $apiVersionSegs);
        preg_match('/api-url: (.*?)\n/s', $docContent, $apiUrlSegs);
        preg_match('/api-method: (.*?)\n/s', $docContent, $apiMethodsSegs);
        preg_match('/api-authorized: (.*?)\n/s', $docContent, $apiAuthorizedSegs);

        return array(
            'apiVersion' => $apiVersionSegs[1],
            'apiUrl' => $apiUrlSegs[1],
            'apiMethod' => $apiMethodsSegs[1],
            'apiAuthorized' => $apiAuthorizedSegs[1],
        );
    }

    private function saveMockedToken($token)
    {
        $mockedTokenStr = 'Accept: application/vnd.edusoho.v2+json; Authorization: Signature '.$token;
        file_put_contents($this->getMockedTokenPath(), '['.date('Y-m-d H:i:s').'] '.$mockedTokenStr);
    }

    private function getMockedTokenPath()
    {
        $biz = $this->getBiz();

        return $biz['kernel.root_dir'].'/data/mockedToken';
    }
}
