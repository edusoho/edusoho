<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;

class ExploreController extends BaseController
{
    const EMPTY_COURSE_SET_IDS = 0;

    public function courseSetsAction(Request $request, $category)
    {
        $conditions = $request->query->all();

        list($conditions, $filter) = $this->getFilter($conditions, 'course');

        list($conditions, $tags) = $this->getConditionsByTags($conditions);
        $conditions = $this->getCourseConditionsByTags($conditions);

        list($conditions, $categoryArray, $categoryParent) = $this->mergeConditionsByCategory($conditions, $category);

        $conditions = $this->getConditionsByVip($conditions, $filter['currentLevelId']);
        $conditions = $this->mergeConditionsByVip($conditions);

        unset($conditions['code']);

        if (isset($conditions['ids']) && empty($conditions['ids'])) {
            $conditions['ids'] = array(-1);
        }

        list($conditions, $orderBy) = $this->getCourseSetSearchOrderBy($conditions);
        $conditions = $this->getCourseSetFilterType($conditions);

        $conditions['parentId'] = 0;
        $conditions['status'] = 'published';

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );

        $courseSets = array();
        if ($orderBy !== 'recommendedSeq') {
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                $orderBy,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        if ($orderBy === 'recommendedSeq') {
            $conditions['recommended'] = 1;
            $recommendCount = $this->getCourseSetService()->countCourseSets($conditions);
            $currentPage = $request->query->get('page') ? $request->query->get('page') : 1;
            $recommendPage = (int) ($recommendCount / 20);
            $recommendLeft = $recommendCount % 20;

            if ($currentPage <= $recommendPage) {
                $courseSets = $this->getCourseSetService()->searchCourseSets(
                    $conditions,
                    $orderBy,
                    ($currentPage - 1) * 20,
                    20
                );
            } elseif (($recommendPage + 1) == $currentPage) {
                $courseSets = $this->getCourseSetService()->searchCourseSets(
                    $conditions,
                    $orderBy,
                    ($currentPage - 1) * 20,
                    20
                );
                $conditions['recommended'] = 0;
                $coursesTemp = $this->getCourseSetService()->searchCourseSets(
                    $conditions,
                    array('createdTime' => 'DESC'),
                    0,
                    20 - $recommendLeft
                );
                $courseSets = array_merge($courseSets, $coursesTemp);
            } else {
                $conditions['recommended'] = 0;
                $courseSets = $this->getCourseSetService()->searchCourseSets(
                    $conditions,
                    array('createdTime' => 'DESC'),
                    (20 - $recommendLeft) + ($currentPage - $recommendPage - 2) * 20,
                    20
                );
            }
        }

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));
        $courses = $this->fillCourseTryLookVideo($courses);

        $tryLookVideoCourses = array_filter($courses, function ($course) {
            return !empty($course['tryLookVideo']);
        });
        $courses = ArrayToolkit::index($courses, 'courseSetId');
        $tryLookVideoCourses = ArrayToolkit::index($tryLookVideoCourses, 'courseSetId');

        array_walk($courseSets, function (&$courseSet) use ($courses, $tryLookVideoCourses) {
            if (isset($tryLookVideoCourses[$courseSet['id']])) {
                $courseSet['course'] = $tryLookVideoCourses[$courseSet['id']];
            } else {
                $courseSet['course'] = $courses[$courseSet['id']];
            }
        });

        $request->query->set('orderBy', $orderBy);

        return $this->render(
            'course-set/explore.html.twig',
            array(
                'courseSets' => $courseSets,
                'category' => $category,
                'filter' => $filter,
                'paginator' => $paginator,
                'consultDisplay' => true,
                'categoryArray' => $categoryArray,
                'categoryParent' => $categoryParent,
                'levels' => $this->findEnabledVipLevels(),
                'tags' => $tags,
            )
        );
    }

    protected function mergeConditionsByCategory($conditions, $category)
    {
        $categoryArray = array();
        $subCategory = empty($conditions['subCategory']) ? null : $conditions['subCategory'];
        $thirdLevelCategory = empty($conditions['selectedthirdLevelCategory']) ? null : $conditions['selectedthirdLevelCategory'];

        if (!empty($subCategory) && empty($thirdLevelCategory)) {
            $conditions['code'] = $subCategory;
        } elseif (!empty($thirdLevelCategory)) {
            $conditions['code'] = $thirdLevelCategory;
        } else {
            $conditions['code'] = $category;
        }

        if (!empty($conditions['code'])) {
            $categoryArray = $this->getCategoryService()->getCategoryByCode($conditions['code']);
            $conditions['categoryId'] = $categoryArray['id'];
            unset($conditions['code']);
        }

        $categoryParent = array();
        if (!empty($categoryArray['parentId'])) {
            $categoryParent = $this->getCategoryService()->getCategory($categoryArray['parentId']);
        }

        return array($conditions, $categoryArray, $categoryParent);
    }

    protected function mergeConditionsByVip($conditions)
    {
        if (empty($conditions['vipLevelIds'])) {
            return $conditions;
        }

        $vipLevelIds = $conditions['vipLevelIds'];
        $courses = $this->getCourseService()->searchCourses(
            array('vipLevelIds' => $vipLevelIds),
            'latest',
            0,
            PHP_INT_MAX
        );
        unset($conditions['vipLevelIds']);

        if (empty($courses)) {
            return $conditions;
        }

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');

        if (empty($conditions['ids'])) {
            $conditions['ids'] = $courseSetIds;

            return $conditions;
        }

        // 当其他查询条件筛选出了课程ID集合后，会员课程的课程ID集合应该和其求并集
        $setIds = array_intersect($courseSetIds, $conditions['ids']);
        $conditions['ids'] = empty($setIds) ? self::EMPTY_COURSE_SET_IDS : $setIds;

        return $conditions;
    }

    protected function findEnabledVipLevels()
    {
        if (!$this->isPluginInstalled('Vip')) {
            return array();
        }

        $levels = $this->getLevelService()->searchLevels(array('enabled' => 1), array('seq' => 'ASC'), 0, 100);

        return ArrayToolkit::index($levels, 'id');
    }

    protected function getConditionsByVip($conditions, $currentLevelId)
    {
        if (!$this->isPluginInstalled('Vip') || $currentLevelId == 'all') {
            return $conditions;
        }

        $levels = $this->getLevelService()->findPrevEnabledLevels($currentLevelId);
        $vipLevelIds = ArrayToolkit::column($levels, 'id');
        $conditions['vipLevelIds'] = array_merge(array($currentLevelId), $vipLevelIds);

        return $conditions;
    }

    public function classroomAction(Request $request, $category)
    {
        $conditions = $request->query->all();

        $conditions['status'] = 'published';
        $conditions['showable'] = 1;

        list($conditions, $tags) = $this->getConditionsByTags($conditions);
        $conditions = $this->getClassroomConditionsByTags($conditions);

        list($conditions, $filter) = $this->getFilter($conditions, 'classroom');

        $conditions = $this->getConditionsByVip($conditions, $filter['currentLevelId']);

        list($conditions, $orderBy) = $this->getClassroomSearchOrderBy($conditions);
        list($conditions, $categoryArray, $categoryParent) = $this->mergeConditionsByCategory($conditions, $category);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            9
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $request->query->set('orderBy', $orderBy);

        return $this->render(
            'classroom/explore.html.twig',
            array(
                'paginator' => $paginator,
                'classrooms' => $classrooms,
                'category' => $category,
                'categoryArray' => $categoryArray,
                'categoryParent' => $categoryParent,
                'filter' => $filter,
                'levels' => $this->findEnabledVipLevels(),
                'tags' => $tags,
            )
        );
    }

    protected function getFilter($conditions, $type)
    {
        $default = array('price' => 'all', 'currentLevelId' => 'all');
        if ($type == 'course') {
            $default['type'] = 'all';
        }

        $filter = !isset($conditions['filter']) ? $default : $conditions['filter'];

        if (isset($filter['price']) && $filter['price'] === 'free') {
            $conditions['price'] = '0.00';
        }

        if (isset($filter['type']) && $filter['type'] != 'all') {
            $conditions['type'] = strip_tags($filter['type']);
        }

        unset($conditions['filter']);

        return array($conditions, $filter);
    }

    protected function getConditionsByTags($conditions)
    {
        $selectedTag = '';
        $selectedTagGroupId = '';
        $tags = array();

        if (empty($conditions['tag'])) {
            return array($conditions, $tags);
        }

        if (!empty($conditions['tag']['tags'])) {
            $tags = $conditions['tag']['tags'];
        }

        if (!empty($conditions['tag']['selectedTag'])) {
            $selectedTag = $conditions['tag']['selectedTag']['tag'];
            $selectedTagGroupId = $conditions['tag']['selectedTag']['group'];
        }

        if (isset($tags[$selectedTagGroupId]) && $tags[$selectedTagGroupId] == $selectedTag) {
            unset($tags[$selectedTagGroupId]);
        } else {
            $tags[$selectedTagGroupId] = $selectedTag;
        }

        $tags = array_filter($tags);
        if (empty($tags)) {
            return array($conditions, $tags);
        }

        $conditions['tagIds'] = array_values($tags);

        return array($conditions, $tags);
    }

    protected function getCourseConditionsByTags($conditions)
    {
        if (empty($conditions['tagIds'])) {
            return $conditions;
        }

        $tagOwnerIds = $this->getTagService()->findOwnerIdsByTagIdsAndOwnerType($conditions['tagIds'], 'course-set');

        $conditions['ids'] = empty($tagOwnerIds) ? array() : $tagOwnerIds;
        unset($conditions['tagIds']);

        return $conditions;
    }

    protected function getClassroomConditionsByTags($conditions)
    {
        if (empty($conditions['tagIds'])) {
            return $conditions;
        }

        $tagOwnerIds = $this->getTagService()->findOwnerIdsByTagIdsAndOwnerType($conditions['tagIds'], 'classroom');

        $conditions['classroomIds'] = empty($tagOwnerIds) ? array(0) : $tagOwnerIds;
        unset($conditions['tagIds']);

        return $conditions;
    }

    protected function getCourseSetSearchOrderBy($conditions)
    {
        $setting = $this->getSettingService()->get('course', array());

        $orderBy = empty($setting['explore_default_orderBy']) ? 'latest' : $setting['explore_default_orderBy'];

        $orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
        unset($conditions['orderBy']);

        return array($conditions, $orderBy);
    }

    protected function getClassroomSearchOrderBy($conditions)
    {
        $setting = $this->getSettingService()->get('classroom');
        $orderBy = empty($setting['explore_default_orderBy']) ? 'createdTime' : $setting['explore_default_orderBy'];

        $orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
        unset($conditions['orderBy']);

        return array($conditions, $orderBy);
    }

    protected function getCourseSetFilterType($conditions)
    {
        if (!$this->isPluginInstalled('Reservation')) {
            $conditions['excludeTypes'] = array('reservation');
        }

        return $conditions;
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getDiscountService()
    {
        return $this->createService('Discount:DiscountService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    protected function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @param $courses
     * @param $course
     *
     * @return mixed
     */
    protected function fillCourseTryLookVideo($courses)
    {
        if (!empty($courses)) {
            $tryLookAbleCourses = array_filter($courses, function ($course) {
                return !empty($course['tryLookable']) && $course['status'] === 'published';
            });
            $tryLookAbleCourseIds = ArrayToolkit::column($tryLookAbleCourses, 'id');
            $activities = $this->getActivityService()->findActivitySupportVideoTryLook($tryLookAbleCourseIds);
            $activityIds = ArrayToolkit::column($activities, 'id');
            $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);
            $tasks = ArrayToolkit::index($tasks, 'activityId');

            $activities = array_filter($activities, function ($activity) use ($tasks) {
                return $tasks[$activity['id']]['status'] === 'published';
            });
            //返回有云视频任务的课程
            $activities = ArrayToolkit::index($activities, 'fromCourseId');

            foreach ($courses as &$course) {
                if (!empty($activities[$course['id']])) {
                    $course['tryLookVideo'] = 1;
                } else {
                    $course['tryLookVideo'] = 0;
                }
            }
            unset($course);
        }

        return $courses;
    }
}
