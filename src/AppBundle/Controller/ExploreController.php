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

        if (!isset($conditions['filter'])) {
            $filter = array(
                'type' => 'all',
                'price' => 'all',
                'currentLevelId' => 'all',
            );
            $conditions['filter'] = $filter;
        } else {
            $filter = $conditions['filter'];
        }

        list($conditions, $tags) = $this->mergeConditionsByTag($conditions);

        list($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent) = $this->mergeConditionsByCategory(
            $conditions,
            $category
        );

        list($conditions, $levels) = $this->mergeConditionsByVip($conditions, $filter['currentLevelId']);

        unset($conditions['code']);

        if (isset($conditions['ids']) && $conditions['ids'] === self::EMPTY_COURSE_SET_IDS) {
            $conditions['ids'] = array(0);
        }

        if ($filter['price'] === 'free') {
            $conditions['price'] = '0.00';
        }

        if ($filter['type'] === 'live') {
            $conditions['type'] = 'live';
        }

        unset($conditions['filter']);

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!isset($courseSetting['explore_default_orderBy'])) {
            $courseSetting['explore_default_orderBy'] = 'latest';
        }

        $orderBy = $courseSetting['explore_default_orderBy'];
        $orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
        unset($conditions['orderBy']);

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

        return $this->render(
            'course-set/explore.html.twig',
            array(
                'courseSets' => $courseSets,
                'category' => $category,
                'filter' => $filter,
                'orderBy' => $orderBy,
                'paginator' => $paginator,
                'consultDisplay' => true,
                'categoryArray' => $categoryArray,
                'categoryArrayDescription' => $categoryArrayDescription,
                'categoryParent' => $categoryParent,
                'levels' => $levels,
                'tags' => $tags,
            )
        );
    }

    protected function mergeConditionsByTag($conditions)
    {
        $tags = array();
        $selectedTag = '';
        $selectedTagGroupId = '';

        if (!empty($conditions['tag'])) {
            if (!empty($conditions['tag']['tags'])) {
                $tags = $conditions['tag']['tags'];
            }

            if (!empty($conditions['tag']['selectedTag'])) {
                $selectedTag = $conditions['tag']['selectedTag']['tag'];
                $selectedTagGroupId = $conditions['tag']['selectedTag']['group'];
            }
        }

        $flag = false;

        foreach ($tags as $groupId => $tagId) {
            if ($groupId == $selectedTagGroupId && $tagId != $selectedTag) {
                $tags[$groupId] = $selectedTag;
                $flag = true;
                break;
            }

            if ($groupId == $selectedTagGroupId && $tagId == $selectedTag) {
                unset($tags[$groupId]);
                $flag = true;
                break;
            }
        }

        if (!$flag) {
            $tags[$selectedTagGroupId] = $selectedTag;
        }

        $tags = array_filter($tags);

        if (!empty($tags)) {
            $conditions['tagIds'] = array_values($tags);
            $conditions['tagIds'] = array_unique($conditions['tagIds']);
            $conditions['tagIds'] = array_filter($conditions['tagIds']);
            $conditions['tagIds'] = array_merge($conditions['tagIds']);

            $tagIdsNum = count($conditions['tagIds']);

            $tagOwnerRelations = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType(
                $conditions['tagIds'],
                'course-set'
            );
            $courseSetIds = ArrayToolkit::column($tagOwnerRelations, 'ownerId');
            $flag = array_count_values($courseSetIds);

            $courseSetIds = array_unique($courseSetIds);

            foreach ($courseSetIds as $key => $setId) {
                if ($flag[$setId] != $tagIdsNum) {
                    unset($courseSetIds[$key]);
                }
            }

            if (empty($courseSetIds)) {
                $conditions['ids'] = self::EMPTY_COURSE_SET_IDS;
            } else {
                $conditions['ids'] = $courseSetIds;
            }

            unset($conditions['tagIds']);
        }

        unset($conditions['tag']);

        return array($conditions, $tags);
    }

    protected function mergeConditionsByCategory($conditions, $category)
    {
        $categoryArray = array();
        $subCategory = empty($conditions['subCategory']) ? null : $conditions['subCategory'];

        $thirdLevelCategory = empty($conditions['selectedthirdLevelCategory']) ? null : $conditions['selectedthirdLevelCategory'];

        if (!empty($conditions['subCategory']) && empty($conditions['selectedthirdLevelCategory'])) {
            $conditions['code'] = $subCategory;
        } elseif (!empty($conditions['selectedthirdLevelCategory'])) {
            $conditions['code'] = $thirdLevelCategory;
        } else {
            $conditions['code'] = $category;
        }

        if (!empty($conditions['code'])) {
            $categoryArray = $this->getCategoryService()->getCategoryByCode($conditions['code']);

            $conditions['categoryId'] = $categoryArray['id'];
        }

        $category = array(
            'category' => $category,
            'subCategory' => $subCategory,
            'thirdLevelCategory' => $thirdLevelCategory,
        );

        if (!$categoryArray) {
            $categoryArrayDescription = array();
        } else {
            $categoryArrayDescription = $categoryArray['description'];
            $categoryArrayDescription = strip_tags($categoryArrayDescription, '');
            $categoryArrayDescription = preg_replace('/ /', '', $categoryArrayDescription);
            $categoryArrayDescription = substr($categoryArrayDescription, 0, 100);
        }

        if (!$categoryArray) {
            $categoryParent = '';
        } else {
            if (!$categoryArray['parentId']) {
                $categoryParent = '';
            } else {
                $categoryParent = $this->getCategoryService()->getCategory($categoryArray['parentId']);
            }
        }

        return array($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent);
    }

    protected function mergeConditionsByVip($conditions, $currentLevelId)
    {
        if (!$this->isPluginInstalled('Vip')) {
            return array($conditions, array());
        }

        $levels = ArrayToolkit::index(
            $this->getLevelService()->searchLevels(array('enabled' => 1), array('seq' => 'ASC'), 0, 100),
            'id'
        );

        if ($currentLevelId !== 'all') {
            $vipLevelIds = ArrayToolkit::column(
                $this->getLevelService()->findPrevEnabledLevels($currentLevelId),
                'id'
            );
            $vipLevelIds = array_merge(array($currentLevelId), $vipLevelIds);
            $courses = $this->getCourseService()->searchCourses(
                array(
                    'vipLevelIds' => $vipLevelIds,
                ),
                'latest',
                0,
                PHP_INT_MAX
            );

            if (empty($courses)) {
                $conditions['ids'] = self::EMPTY_COURSE_SET_IDS;
            } else {
                if (isset($conditions['ids']) && $conditions['ids'] === self::EMPTY_COURSE_SET_IDS) {
                    $conditions['ids'] = self::EMPTY_COURSE_SET_IDS;
                } else {
                    if (isset($conditions['ids']) && $conditions['ids'] !== self::EMPTY_COURSE_SET_IDS) {
                        // 当其他查询条件筛选出了课程ID集合后，会员课程的课程ID集合应该和其求并集
                        $setIds = ArrayToolkit::column($courses, 'courseSetId');
                        $setIds = array_intersect($setIds, $conditions['ids']);
                        $conditions['ids'] = empty($setIds) ? self::EMPTY_COURSE_SET_IDS : $setIds;
                    } else {
                        $conditions['ids'] = ArrayToolkit::column($courses, 'courseSetId');
                    }
                }
            }
        }

        return array($conditions, $levels);
    }

    public function classroomAction(Request $request, $category)
    {
        $conditions = $request->query->all();

        $conditions['status'] = 'published';
        $conditions['showable'] = 1;

        $selectedTag = '';
        $selectedTagGroupId = '';
        $tags = array();
        $categoryArray = array();

        if (!empty($conditions['tag'])) {
            if (!empty($conditions['tag']['tags'])) {
                $tags = $conditions['tag']['tags'];
            }

            if (!empty($conditions['tag']['selectedTag'])) {
                $selectedTag = $conditions['tag']['selectedTag']['tag'];
                $selectedTagGroupId = $conditions['tag']['selectedTag']['group'];
            }
        }

        $tag = array($selectedTagGroupId => $selectedTag);

        $flag = false;

        foreach ($tags as $groupId => $tagId) {
            if ($groupId == $selectedTagGroupId && $tagId != $selectedTag) {
                $tags[$groupId] = $selectedTag;
                $flag = true;
                break;
            }

            if ($groupId == $selectedTagGroupId && $tagId == $selectedTag) {
                unset($tags[$groupId]);
                $flag = true;
                break;
            }
        }

        if (!$flag) {
            $tags[$selectedTagGroupId] = $selectedTag;
        }

        $tags = array_filter($tags);

        if (!empty($tags)) {
            $conditions['tagIds'] = array_values($tags);
            $conditions['tagIds'] = array_unique($conditions['tagIds']);
            $conditions['tagIds'] = array_filter($conditions['tagIds']);
            $conditions['tagIds'] = array_merge($conditions['tagIds']);

            $tagIdsNum = count($conditions['tagIds']);

            $tagOwnerRelations = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType(
                $conditions['tagIds'],
                'classroom'
            );
            $classroomIds = ArrayToolkit::column($tagOwnerRelations, 'ownerId');
            $flag = array_count_values($classroomIds);

            $classroomIds = array_unique($classroomIds);

            foreach ($classroomIds as $key => $classroomId) {
                if ($flag[$classroomId] != $tagIdsNum) {
                    unset($classroomIds[$key]);
                }
            }

            if (empty($classroomIds)) {
                $conditions['classroomIds'] = array(0);
            } else {
                $conditions['classroomIds'] = $classroomIds;
            }

            unset($conditions['tagIds']);
        }

        $subCategory = empty($conditions['subCategory']) ? null : $conditions['subCategory'];
        $thirdLevelCategory = empty($conditions['selectedthirdLevelCategory']) ? null : $conditions['selectedthirdLevelCategory'];

        if (!empty($conditions['subCategory']) && empty($conditions['selectedthirdLevelCategory'])) {
            $conditions['code'] = $subCategory;
        } elseif (!empty($conditions['selectedthirdLevelCategory'])) {
            $conditions['code'] = $thirdLevelCategory;
        } else {
            $conditions['code'] = $category;
        }

        if (!empty($conditions['code'])) {
            $categoryArray = $this->getCategoryService()->getCategoryByCode($conditions['code']);

            $conditions['categoryId'] = $categoryArray['id'];
        }

        $category = array(
            'category' => $category,
            'subCategory' => $subCategory,
            'thirdLevelCategory' => $thirdLevelCategory,
        );

        unset($conditions['code']);

        if (!isset($conditions['filter'])) {
            $conditions['filter'] = array(
                'price' => 'all',
                'currentLevelId' => 'all',
            );
        }

        $filter = $conditions['filter'];

        if ($filter['price'] === 'free') {
            $conditions['price'] = '0.00';
        }

        unset($conditions['filter']);
        $levels = array();

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index(
                $this->getLevelService()->searchLevels(array('enabled' => 1), array('seq' => 'ASC'), 0, 100),
                'id'
            );

            if (!$filter['currentLevelId'] !== 'all') {
                $vipLevelIds = ArrayToolkit::column(
                    $this->getLevelService()->findPrevEnabledLevels($filter['currentLevelId']),
                    'id'
                );
                $conditions['vipLevelIds'] = array_merge(array($filter['currentLevelId']), $vipLevelIds);
            }
        }

        $classroomSetting = $this->getSettingService()->get('classroom');

        if (!isset($classroomSetting['explore_default_orderBy'])) {
            $classroomSetting['explore_default_orderBy'] = 'createdTime';
        }

        $orderBy = empty($conditions['orderBy']) ? $classroomSetting['explore_default_orderBy'] : $conditions['orderBy'];
        unset($conditions['orderBy']);

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

        if (!$categoryArray) {
            $categoryArrayDescription = array();
        } else {
            $categoryArrayDescription = $categoryArray['description'];
            $categoryArrayDescription = strip_tags($categoryArrayDescription, '');
            $categoryArrayDescription = preg_replace('/ /', '', $categoryArrayDescription);
            $categoryArrayDescription = substr($categoryArrayDescription, 0, 100);
        }

        if (!$categoryArray) {
            $categoryParent = '';
        } else {
            if (!$categoryArray['parentId']) {
                $categoryParent = '';
            } else {
                $categoryParent = $this->getCategoryService()->getCategory($categoryArray['parentId']);
            }
        }

        return $this->render(
            'classroom/explore.html.twig',
            array(
                'paginator' => $paginator,
                'classrooms' => $classrooms,
                'path' => 'classroom_explore',
                'category' => $category,
                'subCategory' => $subCategory,
                'categoryArray' => $categoryArray,
                'categoryArrayDescription' => $categoryArrayDescription,
                'categoryParent' => $categoryParent,
                'filter' => $filter,
                'levels' => $levels,
                'orderBy' => $orderBy,
                'tags' => $tags,
                'group' => 'classroom',
            )
        );
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
