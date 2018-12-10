<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\H5SettingService;
use AppBundle\Common\TimeMachine;
use Doctrine\Common\Inflector\Inflector;
use AppBundle\Common\ArrayToolkit;

class H5SettingServiceImpl extends BaseService implements H5SettingService
{
    public function getDiscovery($portal, $mode = 'published', $usage = 'show')
    {
        $discoverySettings = $this->getSettingService()->get("{$portal}_{$mode}_discovery", array());
        if (empty($discoverySettings)) {
            $discoverySettings = $this->getSettingService()->get("{$portal}_published_discovery", array());
        }
        //草稿和发布的设置都为空时获取第一版的默认设置
        if (empty($discoverySettings)) {
            $discoverySettings = $this->getDefaultDiscovery($portal);
        }

        foreach ($discoverySettings as $key => &$discoverySetting) {
            $method = $this->getMethod($discoverySetting['type']);
            $method .= 'Filter';
            $discoverySetting = $this->$method($discoverySetting, $usage);
            if (false === $discoverySetting) {
                unset($discoverySettings[$key]);
            }
        }

        return $discoverySettings;
    }

    public function courseListFilter($discoverySetting, $usage = 'show')
    {
        if ('condition' == $discoverySetting['data']['sourceType']) {
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

        if ('custom' == $discoverySetting['data']['sourceType'] && 'show' == $usage) {
            $courses = $discoverySetting['data']['items'];
            foreach ($courses as $key => $course) {
                $existCourse = $this->getCourseService()->getCourse($course['id']);
                if (empty($existCourse) || 'published' != $existCourse['status']) {
                    unset($discoverySetting['data']['items'][$key]);

                    return $discoverySetting;
                }
                $existCourseSet = $this->getCourseSetService()->getCourseSet($existCourse['courseSetId']);
                if (empty($existCourseSet) || 'published' != $existCourseSet['status']) {
                    unset($discoverySetting['data']['items'][$key]);
                }
            }
        }

        return $discoverySetting;
    }

    public function classroomListFilter($discoverySetting, $usage = 'show')
    {
        if ('condition' == $discoverySetting['data']['sourceType']) {
            if (!empty($discoverySetting['data']['lastDays'])) {
                $timeRange = TimeMachine::getTimeRangeByDays($discoverySetting['data']['lastDays']);
                $conditions['outerStartTime'] = $timeRange['startTime'];
                $conditions['outerEndTime'] = $timeRange['endTime'];
            }

            $conditions = array('status' => 'published', 'showable' => 1);
            $conditions['categoryId'] = $discoverySetting['data']['categoryId'];
            $sort = $this->getSortByStr($discoverySetting['data']['sort']);
            $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
            $classrooms = $this->getClassroomService()->searchClassrooms($conditions, $sort, 0, $limit);
            $discoverySetting['data']['items'] = $classrooms;
        }

        if ('custom' == $discoverySetting['data']['sourceType'] && 'show' == $usage) {
            $classrooms = $discoverySetting['data']['items'];
            foreach ($classrooms as $key => $classroom) {
                $existClassroom = $this->getClassroomService()->getClassroom($classroom['id']);
                if (empty($existClassroom) || 'published' != $existClassroom['status'] || empty($existClassroom['showable'])) {
                    unset($discoverySetting['data']['items'][$key]);
                }
            }
        }

        return $discoverySetting;
    }

    public function slideShowFilter($discoverySetting, $usage = 'show')
    {
        if (!empty($discoverySetting['data']['link'])) {
            $link = $discoverySetting['data']['link'];
            $id = isset($link['target']['id']) ? $link['target']['id'] : 0;
            $target = empty($id) ? null : $this->getTarget($link['type'], $id);
            if (empty($target)) {
                $link['target'] = null;
                $link['url'] = '';
                $discoverySetting['data']['link'] = $link;
            }
        }

        return $discoverySetting;
    }

    public function posterFilter($discoverySetting, $usage = 'show')
    {
        if (!empty($discoverySetting['data']['link'])) {
            $link = $discoverySetting['data']['link'];
            if ('url' != $link['type']) {
                $id = isset($link['target']['id']) ? $link['target']['id'] : 0;
                $target = empty($id) ? null : $this->getTarget($link['type'], $id);
                if (empty($target)) {
                    $link['target'] = null;
                    $link['url'] = '';
                    $discoverySetting['data']['link'] = $link;
                }
            }
        }

        return $discoverySetting;
    }

    public function grouponFilter($discoverySetting, $usage = 'show')
    {
        $activity = $discoverySetting['data']['activity'];
        $remoteActvity = $this->getMarketingPlatformService()->getActivity($activity['id']);
        if (empty($remoteActvity) || isset($remoteActvity['error'])) {
            return false;
        }
        $discoverySetting['data']['activity']['status'] = $remoteActvity['status'];
        $discoverySetting['data']['activity']['name'] = $remoteActvity['name'];
        $discoverySetting['data']['activity']['about'] = $remoteActvity['about'];

        return $discoverySetting;
    }

    public function couponFilter($discoverySetting, $usage = 'show')
    {
        $batches = $discoverySetting['data']['items'];
        $batches = ArrayToolkit::index($batches, 'id');
        $batchIds = ArrayToolkit::column($batches, 'id');
        $user = $this->getCurrentUser();
        $receivedCoupons = array();
        if (!empty($user['id'])) {
            $conditions = array('batchIds' => $batchIds, 'userId' => $user['id']);
            $receivedCoupons = $this->getCouponService()->searchCoupons(
                $conditions,
                array(),
                0,
                $this->getCouponService()->searchCouponsCount($conditions)
            );
        }

        foreach ($receivedCoupons as $coupon) {
            $batches[$coupon['batchId']]['currentUserCoupon'] = $coupon;
        }
        $currentBatches = array();
        if ($this->isPluginInstalled('Coupon')) {
            $currentBatches = $this->getCouponBatchService()->findBatchsByIds($batchIds);
        }
        foreach ($batches as &$batch) {
            $batchId = $batch['id'];
            if (!empty($currentBatches[$batchId])) {
                $batch['money'] = $currentBatches[$batchId]['money'];
                $batch['usedNum'] = $currentBatches[$batchId]['usedNum'];
                $batch['unreceivedNum'] = $currentBatches[$batchId]['unreceivedNum'];
            }
        }
        $discoverySetting['data']['items'] = array_values($batches);

        return $discoverySetting;
    }

    public function getMethod($type)
    {
        $method = Inflector::classify($type);

        return lcfirst($method);
    }

    public function getTarget($type, $id)
    {
        if ('course' == $type) {
            return $this->getCourseService()->getCourse($id);
        }

        if ('classroom' == $type) {
            return $this->getClassroomService()->getClassroom($id);
        }

        return null;
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

    protected function isPluginInstalled($code)
    {
        $app = $this->getAppService()->getAppByCode($code);

        return !empty($app);
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

    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
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

    protected function getMarketingPlatformService()
    {
        return $this->biz->service('Marketing:MarketingPlatformService');
    }

    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    protected function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }

    protected function getCouponBatchService()
    {
        return $this->biz->service('CouponPlugin:Coupon:CouponBatchService');
    }

    protected function getAppService()
    {
        return $this->biz->service('CloudPlatform:AppService');
    }
}
