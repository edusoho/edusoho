<?php

namespace Biz\System\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TimeMachine;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\System\Service\H5SettingService;
use Doctrine\Common\Inflector\Inflector;

class H5SettingServiceImpl extends BaseService implements H5SettingService
{
    public function getDiscovery($portal, $mode = 'published', $usage = 'show')
    {
        $discoverySettings = $this->getSettingService()->get("{$portal}_{$mode}_discovery", []);
        if (empty($discoverySettings)) {
            $discoverySettings = $this->getSettingService()->get("{$portal}_published_discovery", []);
        }
        //草稿和发布的设置都为空时获取第一版的默认设置
        if (empty($discoverySettings)) {
            $discoverySettings = $this->getDefaultDiscovery($portal);
        }

        return $this->filter($discoverySettings, $portal, $usage);
    }

    public function getDiscoveryTemplate($template, $portal, $usage = 'show')
    {
        $class = $this->getTemplateFactory()->getTemplateClass($template);
        $template = $class->getTemplate();

        return $this->filter($template, $portal, $usage);
    }

    public function filter($discoverySettings, $portal, $usage = 'show')
    {
        foreach ($discoverySettings as $key => &$discoverySetting) {
            $method = $this->getMethod($discoverySetting['type']);
            $method .= 'Filter';
            $discoverySetting = $this->$method($discoverySetting, $portal, $usage);
            if (false === $discoverySetting) {
                unset($discoverySettings[$key]);
            }
        }

        return $discoverySettings;
    }

    public function getAppDiscoveryVersion()
    {
        $appDiscoverySetting = $this->getSettingService()->get('app_discovery', []);

        return empty($appDiscoverySetting['version']) ? 0 : (int) $appDiscoverySetting['version'];
    }

    public function searchFilter($discoverySetting, $portal, $usage = 'show')
    {
        return $discoverySetting;
    }

    public function courseListFilter($discoverySetting, $portal, $usage = 'show')
    {
        if ('condition' == $discoverySetting['data']['sourceType']) {
            $conditions = ['parentId' => 0, 'status' => 'published', 'courseSetStatus' => 'published', 'excludeTypes' => ['reservation']];
            if (!empty($discoverySetting['data']['lastDays'])) {
                $timeRange = TimeMachine::getTimeRangeByDays($discoverySetting['data']['lastDays']);
                $conditions['outerStartTime'] = $timeRange['startTime'];
                $conditions['outerEndTime'] = $timeRange['endTime'];
            }
            $conditions['categoryId'] = $discoverySetting['data']['categoryId'];
            $sort = $this->getSortByStr($discoverySetting['data']['sort']);
            $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
            $courses = $this->getCourseService()->searchBySort($conditions, $sort, 0, $limit);
            $discoverySetting['data']['items'] = $courses;
        }

        if ('custom' == $discoverySetting['data']['sourceType']) {
            $courses = $discoverySetting['data']['items'];
            foreach ($courses as $key => $course) {
                $existCourse = $this->getCourseService()->getCourse($course['id']);
                $discoverySetting['data']['items'][$key] = $existCourse;
                if (empty($existCourse)) {
                    unset($discoverySetting['data']['items'][$key]);
                    continue;
                }
                if ('show' == $usage && 'published' != $existCourse['status']) {
                    unset($discoverySetting['data']['items'][$key]);
                    continue;
                }
                $existCourseSet = $this->getCourseSetService()->getCourseSet($existCourse['courseSetId']);
                if ('show' == $usage && 'published' != $existCourseSet['status']) {
                    unset($discoverySetting['data']['items'][$key]);
                    continue;
                }
            }
        }
        $discoverySetting['data']['items'] = array_values($discoverySetting['data']['items']);

        return $discoverySetting;
    }

    public function openCourseListFilter($discoverySetting, $portal, $usage = 'show')
    {
        if ('condition' == $discoverySetting['data']['sourceType']) {
            $conditions = [
                'categoryId' => isset($discoverySetting['data']['categoryId']) ? $discoverySetting['data']['categoryId'] : 0,
                'limitDays' => isset($discoverySetting['data']['limitDays']) ? $discoverySetting['data']['limitDays'] : 0,
            ];

            $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
            $discoverySetting['data']['items'] = $this->getOpenCourseService()->searchAndSortLiveCourses(
                $conditions,
                0,
                $limit
            );
        }

        if ('custom' == $discoverySetting['data']['sourceType']) {
            $courses = $discoverySetting['data']['items'];
            foreach ($courses as $key => $course) {
                $existCourse = $this->getOpenCourseService()->getCourse($course['id']);
                $discoverySetting['data']['items'][$key] = $existCourse;
                if (empty($existCourse)) {
                    unset($discoverySetting['data']['items'][$key]);
                    continue;
                }
                if ('show' == $usage && 'published' != $existCourse['status']) {
                    unset($discoverySetting['data']['items'][$key]);
                    continue;
                }

                $existLesson = $this->getOpenCourseService()->getCourseLesson($course['id'], $course['lesson']['id']);
                $discoverySetting['data']['items'][$key]['lesson'] = $existLesson;

                if (empty($existLesson) || ('show' == $usage && 'published' != $existLesson['status'])) {
                    unset($discoverySetting['data']['items'][$key]);
                }
            }
        }
        $discoverySetting['data']['items'] = array_values($discoverySetting['data']['items']);

        return $discoverySetting;
    }

    public function classroomListFilter($discoverySetting, $portal, $usage = 'show')
    {
        if ('condition' == $discoverySetting['data']['sourceType']) {
            $conditions = ['status' => 'published', 'showable' => 1];
            if (!empty($discoverySetting['data']['lastDays'])) {
                $timeRange = TimeMachine::getTimeRangeByDays($discoverySetting['data']['lastDays']);
                $conditions['outerStartTime'] = $timeRange['startTime'];
                $conditions['outerEndTime'] = $timeRange['endTime'];
            }
            $conditions['categoryId'] = $discoverySetting['data']['categoryId'];
            $sort = $this->getSortByStr($discoverySetting['data']['sort']);
            $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
            $classrooms = $this->getClassroomService()->searchClassrooms($conditions, $sort, 0, $limit);
            $discoverySetting['data']['items'] = $classrooms;
        }

        if ('custom' == $discoverySetting['data']['sourceType']) {
            $classrooms = $discoverySetting['data']['items'];
            foreach ($classrooms as $key => $classroom) {
                $existClassroom = $this->getClassroomService()->getClassroom($classroom['id']);
                $discoverySetting['data']['items'][$key] = $existClassroom;
                if (empty($existClassroom)) {
                    unset($discoverySetting['data']['items'][$key]);
                    continue;
                }

                if ('show' == $usage && 'published' != $existClassroom['status'] || empty($existClassroom['showable'])) {
                    unset($discoverySetting['data']['items'][$key]);
                    continue;
                }
            }
        }
        $discoverySetting['data']['items'] = array_values($discoverySetting['data']['items']);

        return $discoverySetting;
    }

    public function slideShowFilter($discoverySetting, $portal, $usage = 'show')
    {
        foreach ($discoverySetting['data'] as &$slideShow) {
            if (!empty($slideShow['link'])) {
                $link = $slideShow['link'];
                $id = isset($link['target']['id']) ? $link['target']['id'] : 0;
                $target = empty($id) ? null : $this->getTarget($link['type'], $id);
                if (empty($target)) {
                    $link['target'] = null;
                    $link['url'] = 'url' != $link['type'] ? '' : $link['url'];
                    $slideShow['link'] = $link;
                }
            }
        }

        return $discoverySetting;
    }

    public function graphicNavigationFilter($discoverySetting, $portal, $usage = 'show')
    {
        $schema = (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS'])) ? 'https' : 'http';

        foreach ($discoverySetting['data'] as &$navigation) {
            if (!empty($navigation['image']['url'])) {
                $navigation['image']['uri'] = !empty($navigation['image']['uri']) ? $navigation['image']['uri'] : $navigation['image']['url'];
            }
            if (!empty($navigation['link'])) {
                if ('h5' == $portal) {
                    $navigation['link']['url'] = $schema.'://'.$_SERVER['HTTP_HOST'].'/h5/index.html#/'.$navigation['link']['type'].'/explore';
                } else {
                    $navigation['link']['url'] = $schema.'://'.$_SERVER['HTTP_HOST'].'/h5/index.html#/'.$navigation['link']['type'].'/explore/new';
                }
                $navigation['link']['conditions'] = ['categoryId' => !empty($navigation['link']['categoryId']) ? $navigation['link']['categoryId'] : 0];
            }
        }

        return $discoverySetting;
    }

    public function posterFilter($discoverySetting, $portal, $usage = 'show')
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

    public function grouponFilter($discoverySetting, $portal, $usage = 'show')
    {
        $activity = $discoverySetting['data']['activity'];
        try {
            $remoteActvity = $this->getMarketingPlatformService()->getActivity($activity['id']);
        } catch (\Exception $e) {
            $remoteActvity = null;
        }
        if (empty($remoteActvity) || isset($remoteActvity['error'])) {
            return false;
        }
        $discoverySetting['data']['activity'] = $remoteActvity;

        return $discoverySetting;
    }

    public function seckillFilter($discoverySetting, $portal, $usage = 'show')
    {
        $activity = $discoverySetting['data']['activity'];
        try {
            $remoteActvity = $this->getMarketingPlatformService()->getActivity($activity['id']);
        } catch (\Exception $e) {
            $remoteActvity = null;
        }
        if (empty($remoteActvity) || isset($remoteActvity['error'])) {
            return false;
        }
        $discoverySetting['data']['activity'] = $remoteActvity;

        return $discoverySetting;
    }

    public function cutFilter($discoverySetting, $portal, $usage = 'show')
    {
        $activity = $discoverySetting['data']['activity'];
        try {
            $remoteActvity = $this->getMarketingPlatformService()->getActivity($activity['id']);
        } catch (\Exception $e) {
            $remoteActvity = null;
        }
        if (empty($remoteActvity) || isset($remoteActvity['error'])) {
            return false;
        }
        $discoverySetting['data']['activity'] = $remoteActvity;

        return $discoverySetting;
    }

    public function couponFilter($discoverySetting, $portal, $usage = 'show')
    {
        $batches = $discoverySetting['data']['items'];
        $batches = ArrayToolkit::index($batches, 'id');
        $batchIds = ArrayToolkit::column($batches, 'id');
        if ('show' == $usage) {
            $batches = $this->getCouponBatchService()->fillUserCurrentCouponByBatches($batches);
        }

        $currentBatches = [];
        $currentBatches = $this->getCouponBatchService()->findBatchsByIds($batchIds);
        foreach ($batches as $key => &$batch) {
            if ('discount' == $batch['type']) {
                $batch['rate'] = strval(floatval($batch['rate']));
            }

            $batchId = $batch['id'];
            if (empty($currentBatches[$batchId])) {
                unset($batches[$key]);
                continue;
            }

            if ('show' == $usage && $currentBatches[$batchId]['deadline'] + 86400 < time() && $currentBatches[$batchId]['deadline'] > 0) {
                unset($batches[$key]);
                continue;
            }
            if (!empty($currentBatches[$batchId])) {
                $batch['money'] = $currentBatches[$batchId]['money'];
                $batch['usedNum'] = $currentBatches[$batchId]['usedNum'];
                $batch['unreceivedNum'] = $currentBatches[$batchId]['unreceivedNum'];
                if (isset($currentBatches[$batchId]['fixedDay'])) {
                    $batch['fixedDay'] = intval($currentBatches[$batchId]['fixedDay']);
                }
                if (isset($currentBatches[$batchId]['deadlineMode'])) {
                    $batch['deadlineMode'] = $currentBatches[$batchId]['deadlineMode'];
                }
                if ($this->isPluginInstalled('Vip') && 'vip' == $currentBatches[$batchId]['targetType'] && !empty($currentBatches[$batchId]['targetId'])) {
                    $batch['target'] = $this->getLevelService()->getLevel($currentBatches[$batchId]['targetId']);
                }
            }
        }
        $discoverySetting['data']['items'] = array_values($batches);

        return $discoverySetting;
    }

    public function vipFilter($discoverySetting, $portal, $usage = 'show')
    {
        if ($this->isPluginInstalled('Vip')) {
            try {
                $levels = $this->getLevelService()->findEnabledLevels();
                foreach ($levels as &$level) {
                    $level['freeCourseNum'] = $this->getLevelService()->getFreeCourseNumByLevelId($level['id']);
                    $level['freeClassroomNum'] = $this->getLevelService()->getFreeClassroomNumByLevelId($level['id']);
                }
                $discoverySetting['data']['items'] = 'desc' == $discoverySetting['data']['sort'] ? array_reverse($levels) : $levels;
            } catch (\Exception $e) {
                throw CommonException::NOTFOUND_METHOD();
            }
        }

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

        if ('vip' == $type) {
            return $this->getLevelService()->getLevel($id);
        }

        return null;
    }

    public function getCourseCondition($portal, $mode = 'published')
    {
        $group = $this->getCategoryService()->getGroupByCode('course');

        return [
            'title' => '所有课程',
            [
                'type' => 'category',
                'moduleType' => 'tree',
                'text' => '分类',
                'data' => $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], 0),
            ],
            [
                'type' => 'courseType',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => [
                    [
                        'type' => 'normal',
                        'text' => '课程',
                    ],
                    [
                        'type' => 'live',
                        'text' => '直播',
                    ],
                ],
            ],
            [
                'type' => 'sort',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => [
                    [
                        'type' => 'recommendedSeq',
                        'text' => '推荐',
                    ],
                    [
                        'type' => '-studentNum',
                        'text' => '热门',
                    ],
                    [
                        'type' => '-createdTime',
                        'text' => '最新',
                    ],
                ],
            ],
        ];
    }

    protected function getSortByStr($sortStr)
    {
        if ($sortStr) {
            $explodeSort = explode(',', $sortStr);

            $sort = [];
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

        return [];
    }

    protected function isPluginInstalled($code)
    {
        $app = $this->getAppService()->getAppByCode($code);

        return !empty($app);
    }

    public function getDefaultDiscovery($portal)
    {
        $result = [];

        if (in_array($portal, ['h5', 'apps'])) {
            $posters = $this->getBlockService()->getPosters();
            $slides = [];
            foreach ($posters as $poster) {
                $slide = [
                    'title' => '',
                    'image' => [
                        'id' => 0,
                        'uri' => $poster['image'],
                        'size' => '',
                        'createdTime' => 0,
                    ],
                    'link' => [
                        'type' => 'url',
                        'target' => null,
                        'url' => $poster['link']['url'],
                    ],
                ];
                $slides[] = $slide;
            }

            $result = [
                'slide-1' => [
                    'type' => 'slide_show',
                    'moduleType' => 'slide-1',
                    'data' => $slides,
                ],
            ];
        }

        return array_merge($result, [
            'courseList-1' => [
                'type' => 'course_list',
                'moduleType' => 'courseList-1',
                'data' => [
                    'title' => '热门课程',
                    'sourceType' => 'condition',
                    'categoryId' => 0,
                    'sort' => '-studentNum',
                    'lastDays' => 0,
                    'limit' => 4,
                    'items' => [],
                ],
            ],
            'courseList-2' => [
                'type' => 'course_list',
                'moduleType' => 'courseList-2',
                'data' => [
                    'title' => '推荐课程',
                    'sourceType' => 'condition',
                    'categoryId' => 0,
                    'sort' => 'recommendedSeq',
                    'lastDays' => 0,
                    'limit' => 4,
                    'items' => [],
                ],
            ],
        ]);
    }

    protected function getTemplateFactory()
    {
        return $this->biz['template_factory'];
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

    protected function getLevelService()
    {
        return $this->biz->service('VipPlugin:Vip:LevelService');
    }

    protected function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }

    protected function getCouponBatchService()
    {
        return $this->biz->service('Coupon:CouponBatchService');
    }

    protected function getAppService()
    {
        return $this->biz->service('CloudPlatform:AppService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->biz->service('OpenCourse:OpenCourseService');
    }
}
