<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\H5SettingService;
use AppBundle\Common\TimeMachine;

class H5SettingServiceImpl extends BaseService implements H5SettingService
{
    public function getDiscovery($portal, $mode = 'published')
    {
        $discoverySettings = $this->getSettingService()->get("{$portal}_{$mode}_discovery", array());
        if (empty($discoverySettings)) {
            $discoverySettings = $this->getSettingService()->get("{$portal}_published_discovery", array());
        }
        foreach ($discoverySettings as &$discoverySetting) {
            if ('course_list' == $discoverySetting['type'] && 'condition' == $discoverySetting['data']['sourceType']) {
                if (!empty($discoverySetting['data']['lastDays'])) {
                    $timeRange = TimeMachine::getTimeRangeByDays($discoverySetting['data']['lastDays']);
                    $conditions['otherStartTime'] = $timeRange['startTime'];
                    $conditions['otherEndTime'] = $timeRange['endTime'];
                }

                $conditions = array('parentId' => 0, 'status' => 'published', 'courseSetStatus' => 'published', 'excludeTypes' => array('reservation'));
                $conditions['categoryId'] = $discoverySetting['data']['categoryId'];
                $sort = $this->getSortByStr($discoverySetting['data']['sort']);
                $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
                $courses = $this->getCourseService()->searchBySort($conditions, $sort, 0, $limit);
                $discoverySetting['data']['items'] = $courses;
            }
        }

        return $discoverySettings;
    }

    public function getCourseCondition($portal, $mode = 'published')
    {
        $group = $this->getCategoryService()->getGroupByCode('course');

        return array(
            'title' => '所有课程',
            array(
                'type' => 'category',
                'moduleType' => 'tree',
                'text' => '分类',
                'data' => $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], 0),
            ),
            array(
                'type' => 'courseType',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => array(
                    array(
                        'type' => 'normal',
                        'text' => '课程',
                    ),
                    array(
                        'type' => 'live',
                        'text' => '直播',
                    ),
                ),
            ),
            array(
                'type' => 'sort',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => array(
                    array(
                        'type' => 'recommendedSeq',
                        'text' => '推荐',
                    ),
                    array(
                        'type' => '-studentNum',
                        'text' => '热门',
                    ),
                    array(
                        'type' => '-createdTime',
                        'text' => '最新',
                    ),
                ),
            ),
        );
    }

    protected function getSortByStr($sortStr)
    {
        if ($sortStr) {
            $explodeSort = explode(',', $sortStr);

            $sort = array();
            foreach ($explodeSort as $part) {
                $prefix = substr($part, 0, 1);
                $field = str_replace('-', '', $part);
                if ('-' == $prefix) {
                    $sort[$field] = 'DESC';
                } else {
                    $sort[$field] = 'ASC';
                }
            }

            return $sort;
        }

        return array();
    }

    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    protected function getCategoryService()
    {
        return $this->biz->service('Taxonomy:CategoryService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
