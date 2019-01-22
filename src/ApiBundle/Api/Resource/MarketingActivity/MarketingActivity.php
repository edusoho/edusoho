<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;

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
        $conditions = $request->query->all();
        if (isset($conditions['name'])) {
            $conditions['name_like'] = $conditions['name'];
            unset($conditions['name']);
        }

        if (isset($conditions['itemType'])) {
            $conditions['item_type'] = $conditions['itemType'];
            unset($conditions['itemType']);
        }

        if (isset($conditions['productRemaind_GT'])) {
            $conditions['product_remaind_GT'] = $conditions['productRemaind_GT'];
            unset($conditions['productRemaind_GT']);
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions['page'] = ceil(($offset + 1) / $limit);
        $conditions['limit'] = $limit;
        // 微营销接口传递的参数，表明搜索的活动是已经设置了规则的数据
        $conditions['is_set_rule'] = 1;

        $user = $this->getCurrentUser();
        $client = MarketingAPIFactory::create();

        $pages = $client->get(
            '/activities',
            $conditions,
            array('MERCHANT-USER-ID: '.$user['id'])
        );

        return $this->makePagingObject($pages['data'], $pages['paging']['total'], $offset, $limit);
    }
}
