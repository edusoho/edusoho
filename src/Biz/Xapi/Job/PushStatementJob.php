<?php

namespace Biz\Xapi\Job;

use Biz\System\Service\SettingService;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use AppBundle\Common\ArrayToolkit;
use Guzzle\Http\Client;
use QiQiuYun\SDK\Auth;

class PushStatementJob extends AbstractJob
{
    public function execute()
    {
        try{
            $condition = array(
                'status' => 'created',
            );
            $statements = $this->getXapiService()->searchStatements($condition, array('created_time' => 'ASC'), 0, 100);
            $statementIds = ArrayToolkit::column($statements, 'id');

            $pushStatements = array();
            $pushData = array();
            foreach ($statements as $statement) {
                $push = $this->biz["xapi.push.{$statement['verb']}_{$statement['target_type']}"];
                $pushStatement = $push->package($statement);
                $pushStatements[] = $pushStatement;
                $pushData[$statement['id']] = $pushStatement;
            }

            if (empty($pushStatements)) {
                return;
            }

            $this->getXapiService()->updateStatementsPushingByStatementIds($statementIds);
            $result = $this->createXAPIService()->pushStatements($pushStatements);
            file_put_contents('1.txt', json_encode($result).PHP_EOL, FILE_APPEND);

            if ($result) {
                $this->getXapiService()->updateStatementsPushedAndDataByStatementData($pushData);
            }
        } catch (\Exception $e) {
            file_put_contents('2.txt', $e->getMessage());
        }

    }

    public function createXAPIService()
    {
        $settings = $this->getSettingService()->get('storage', array());
        $siteSettings = $this->getSettingService()->get('site', array());

        $siteName = empty($siteSettings['name']) ? '' : $siteSettings['name'];
        $siteUrl = empty($siteSettings['url']) ? '' : $siteSettings['url'];
        $accessKey = empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'];
        $secretKey = empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'];
        $auth = new Auth('9DdikSDLhmObBhE0t3mhN9UUl8FW2Zdh', 'jNqSV44Fx5kxBFc4VI840pLk8D6QeO86');

        return new \QiQiuYun\SDK\Service\XAPIService($auth, array(
            'base_uri' => 'http://192.168.4.214:8769/v1/xapi/', //推送的URL需要配置
            'school' => array(
                'accessKey' => $accessKey,
                'url' => $siteUrl,
                'name' => $siteName,
            ),
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

//    protected function pushStatements($statements)
//    {
//        $pushdStatements = array();
//        foreach ($statements as $statement) {
//            $pushStatement = ArrayToolkit::parts($statement['data'], array('actor', 'verb', 'object', 'result', 'context'));
//            $pushStatement['timestamp'] = time();
//            $pushStatement['id'] = $statement['uuid'];
//            $pushdStatements[] = $pushStatement;
//        }
    //
//        $client = new Client();
//        $request = $client->post($this->biz['xapi.options']['getway'], array(
//            'Content-type' => 'application/json; charset=utf-8',
//        ), json_encode($pushdStatements));
    //
//        $response = $request->send();
//        if ($response->getStatusCode() == 200) {
//            return true;
//        }
    //
//        return false;
//    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }
}
