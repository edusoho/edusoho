<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

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
        $activities = array(
            array(
                'id' => 1,
                'name' => '活动名称',
                'type' => 'groupon',
                'status' => 'ongoing',
                'originPrice' => 100,
                'price' => 1,
                'itemId' => 19,
                'itemType' => 'course',
                'createdTime' => time(),
            ),
        );
        $activityGroups = ArrayToolkit::group($activities, 'itemType');
        $activities = array();
        foreach ($activityGroups as $key => &$groups) {
            $this->getOCUtil()->multiple($groups, array('itemId'), $key);
            $activities = array_merge($activities, $groups);
        }

        $total = 33;

        return $this->makePagingObject($activities, $total, $offset, $limit);
    }
}
