<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;
use ApiBundle\Api\Resource\MarketingActivity\MarketingActivityFilter;
use AppBundle\Common\ArrayToolkit;

class CourseMarketingActivity extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $conditions = array(
            'item_type' => 'course',
            'item_source_id' => $courseId,
            'statuses' => 'ongoing,unstart',
            'is_set_rule' => 1,
            'types' => 'cut,groupon,seckill',
            'format' => 'nonepage',
        );

        $systemUser = $this->getUserService()->getUserByType('system');
        $this->getMarketingPlatformService()->simpleLogin($systemUser['id']);
        $client = MarketingAPIFactory::create();

        try {
            $activities = $client->get(
                '/activities',
                $conditions,
                array('MERCHANT-USER-ID: '.$systemUser['id'])
            );
            $marketingActivityFilter = new MarketingActivityFilter();
            $marketingActivityFilter->filters($activities);

            $activities = ArrayToolkit::group($activities, 'type');

            foreach ($activities as $type => $activity) {
                $firstActivity = current($activity);
                if ('seckill' == $firstActivity['type'] && 0 == $firstActivity['productRemaind']) {
                    unset($activities[$type]);
                } else {
                    $activities[$type] = current($activity);
                }
            }

            return $activities;
        } catch (\Exception $e) {
            return array();
        }
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    protected function getMarketingPlatformService()
    {
        return $this->service('Marketing:MarketingPlatformService');
    }
}
