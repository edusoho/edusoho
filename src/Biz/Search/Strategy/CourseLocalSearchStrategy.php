<?php

namespace Biz\Search\Strategy;

use AppBundle\Common\PluginToolkit;
use Biz\CloudPlatform\Service\AppService;
use Biz\Course\Service\CourseSetService;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class CourseLocalSearchStrategy implements LocalSearchStrategy
{
    use LocalSearchStrategyTrait;

    public function buildSearchConditions($keyword, $filter)
    {
        $conditions = [
            'status' => 'published',
            'title' => $keyword,
            'parentId' => 0,
        ];

        if ('vip' == $filter) {
            $conditions['vipLevelIds'] = $this->getVipLevelIds();
        } elseif ('live' == $filter) {
            $conditions['type'] = 'live';
        } elseif ('free' == $filter) {
            $conditions['minCoursePrice'] = '0.00';
        }

        $this->conditions = $this->filterCourseConditions($conditions);
    }

    public function count()
    {
        return $this->getCourseSetService()->countCourseSets($this->conditions);
    }

    public function search($start, $limit)
    {
        return $this->getCourseSetService()->searchCourseSets(
            $this->conditions,
            ['updatedTime' => 'desc'],
            $start,
            $limit
        );
    }

    private function getVipLevelIds()
    {
        $vip = $this->getAppService()->findInstallApp('Vip');
        $isShowVipSearch = $vip && version_compare($vip['version'], '1.0.7', '>=');

        $vipLevelIds = [];
        if (!$isShowVipSearch) {
            return $vipLevelIds;
        }

        $currentUserVip = $this->getVipService()->getMemberByUserId($this->biz['user']->getId());
        if (!empty($currentUserVip) && isset($currentUserVip['levelId'])) {
            $currentUserVipLevel = $this->getLevelService()->getLevel($currentUserVip['levelId']);
            $vipLevels = $this->getLevelService()->findAllLevelsLessThanSeq($currentUserVipLevel['seq']);
            $vipLevelIds = array_column($vipLevels, 'id');
        }

        return $vipLevelIds;
    }

    private function filterCourseConditions($conditions)
    {
        if (!PluginToolkit::isPluginInstalled('Reservation')) {
            $conditions['excludeTypes'] = ['reservation'];
        }

        return $conditions;
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return AppService
     */
    private function getAppService()
    {
        return $this->biz->service('CloudPlatform:AppService');
    }

    /**
     * @return LevelService
     */
    private function getLevelService()
    {
        return $this->biz->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return VipService
     */
    private function getVipService()
    {
        return $this->biz->service('VipPlugin:Vip:VipService');
    }
}
