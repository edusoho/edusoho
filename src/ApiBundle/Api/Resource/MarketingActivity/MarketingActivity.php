<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification2;
use Biz\Marketing\Util\MarketingUtils;

class MarketingActivity extends AbstractResource
{
    /**
     * @param ApiRequest $request
     *
     * @return mixed
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        //搜索条件：学校id,type,name,status,itemType
        // $activities = array(
        //     array(
        //         'id' => 1,
        //         'name' => '活动名称',
        //         'type' => 'groupon',
        //         'status' => 'ongoing',
        //         'originPrice' => 100,
        //         'price' => 1,
        //         'itemId' => 19,
        //         'itemType' => 'course',
        //         'createdTime' => time(),
        //     ),
        // );
        $siteInfo = MarketingUtils::getSiteInfo($this->getSettingService(), $this->getWebExtension());
        $client = $this->createMarketingClient();
        $activities = $client->get('/activities', array(
            'site' => $siteInfo,
            'url' => $merchantUrl,
            'user_id' => $user['id'],
            'user_name' => $user['nickname'],
            'user_avatar' => $this->getWebExtension()->getFurl($user['largeAvatar'], 'avatar.png'),
            'entry' => $entry,
        ));
        var_dump($activities);
        exit();
        $activityGroups = ArrayToolkit::group($activities, 'itemType');
        $activities = array();
        foreach ($activityGroups as $key => &$groups) {
            $this->getOCUtil()->multiple($groups, array('itemId'), $key);
            $activities = array_merge($activities, $groups);
        }

        $total = 33;

        return $this->makePagingObject($activities, $total, $offset, $limit);
    }

    private function createMarketingClient()
    {
        $storage = $this->getSettingService()->get('storage', array());
        $developerSetting = $this->getSettingService()->get('developer', array());

        $marketingDomain = !empty($developerSetting['marketing_domain']) ? $developerSetting['marketing_domain'] : 'http://wyx.edusoho.cn';

        $config = array(
            'accessKey' => $storage['cloud_access_key'],
            'secretKey' => $storage['cloud_secret_key'],
            'endpoint' => $marketingDomain.'/merchant',
        );
        $spec = new JsonHmacSpecification2('sha1');
        $client = new RestApiClient($config, $spec);

        return $client;
    }

    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
