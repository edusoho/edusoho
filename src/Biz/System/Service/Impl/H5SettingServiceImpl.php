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
        //草稿和发布的设置都为空时获取第一版的默认设置
        if (empty($discoverySettings)) {
            $discoverySettings = $this->getDefaultDiscovery($portal);
        }

        foreach ($discoverySettings as &$discoverySetting) {
            if ('course_list' == $discoverySetting['type'] && 'condition' == $discoverySetting['data']['sourceType']) {
                if (!empty($discoverySetting['data']['lastDays'])) {
                    $timeRange = TimeMachine::getTimeRangeByDays($discoverySetting['data']['lastDays']);
                    $conditions['outerStartTime'] = $timeRange['startTime'];
                    $conditions['outerEndTime'] = $timeRange['endTime'];
                }

                $conditions = array('parentId' => 0, 'status' => 'published', 'courseSetStatus' => 'published', 'excludeTypes' => array('reservation'));
                $conditions['categoryId'] = $discoverySetting['data']['categoryId'];
                $sort = $this->getSortByStr($discoverySetting['data']['sort']);
                $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
                $courses = $this->getCourseService()->searchBySort($conditions, $sort, 0, $limit);
                $discoverySetting['data']['items'] = $courses;
            }

            if (in_array($discoverySetting['type'], array('poster', 'slide_show')) && !empty($discoverySetting['data']['link']) && 'target' == $discoverySetting['data']['link']['type']) {
                $link = $discoverySetting['data']['link'];
                $course = $this->getCourseService()->getCourse($link['target']['id']);
                if (empty($course)) {
                    $link['target'] = null;
                    $link['url'] = '';
                }
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

    public function getDefaultDiscovery($portal)
    {
        $result = array();
        if ('h5' == $portal) {
            $posters = $this->getBlockService()->getPosters();
            $slides = array();
            foreach ($posters as $poster) {
                $slide = array(
                    'title' => '',
                    'image' => array(
                        'id' => 0,
                        'uri' => $poster['image'],
                        'size' => '',
                        'createdTime' => 0,
                    ),
                    'link' => array(
                        'type' => 'url',
                        'target' => null,
                        'url' => $poster['link']['url'],
                    ),
                );
                $slides[] = $slide;
            }

            $result = array(
                'slide-1' => array(
                    'type' => 'slide_show',
                    'moduleType' => 'slide-1',
                    'data' => $slides,
                ),
            );
        }

        return array_merge($result, array(
            'courseList-1' => array(
                'type' => 'course_list',
                'moduleType' => 'courseList-1',
                'data' => array(
                    'title' => '热门课程',
                    'sourceType' => 'condition',
                    'categoryId' => 0,
                    'sort' => '-studentNum',
                    'lastDays' => 0,
                    'limit' => 4,
                    'items' => array(),
                ),
            ),
            'courseList-2' => array(
                'type' => 'course_list',
                'moduleType' => 'courseList-2',
                'data' => array(
                    'title' => '推荐课程',
                    'sourceType' => 'condition',
                    'categoryId' => 0,
                    'sort' => 'recommendedSeq',
                    'lastDays' => 0,
                    'limit' => 4,
                    'items' => array(),
                ),
            ),
        ));
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

    protected function getBlockService()
    {
        return $this->biz->service('Content:BlockService');
    }
}
