<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class ExploreController extends CourseBaseController
{
    public function courseAction(Request $request, $category)
    {
        $conditions    = $request->query->all();

        $selectedTag        = '';
        $selectedTagGroupId = '';
        $tags               = array();
        $categoryArray      = array();
        $levels             = array();

        if (!empty($conditions['tag'])) {
            if (!empty($conditions['tag']['tags'])) {
                $tags = $conditions['tag']['tags'];
            }

            if (!empty($conditions['tag']['selectedTag'])) {
                $selectedTag        = $conditions['tag']['selectedTag']['tag'];
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

            $tagOwnerRelations = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType($conditions['tagIds'], 'course');
            $courseIds = ArrayToolkit::column($tagOwnerRelations, 'ownerId');
            $flag = array_count_values($courseIds);

            $courseIds = array_unique($courseIds);

            foreach ($courseIds as $key => $courseId) {
                if ($flag[$courseId] != $tagIdsNum) {
                    unset($courseIds[$key]);
                }
            }

            if (empty($courseIds)) {
                $conditions['courseIds'] = array(0);
            } else {
                $conditions['courseIds'] = $courseIds;
            }

            unset($conditions['tagIds']);
        }

        unset($conditions['tag']);

        $subCategory        = empty($conditions['subCategory']) ? null : $conditions['subCategory'];
        $thirdLevelCategory = empty($conditions['selectedthirdLevelCategory']) ? null : $conditions['selectedthirdLevelCategory'];

        if (!empty($conditions['subCategory']) && empty($conditions['selectedthirdLevelCategory'])) {
            $conditions['code'] = $subCategory;
        } elseif (!empty($conditions['selectedthirdLevelCategory']) ) {
            $conditions['code'] = $thirdLevelCategory;
        } else {
            $conditions['code'] = $category;
        }

        if (!empty($conditions['code'])) {
            $categoryArray = $this->getCategoryService()->getCategoryByCode($conditions['code']);

            $conditions['categoryId'] = $categoryArray['id'];
        }

        $category = array(
            'category'           => $category,
            'subCategory'        => $subCategory,
            'thirdLevelCategory' => $thirdLevelCategory,
        );

        unset($conditions['code']);

        if (!isset($conditions['filter'])) {
            $conditions['filter'] = array(
                'type'           => 'all',
                'price'          => 'all',
                'currentLevelId' => 'all'
            );
        }

        $filter = $conditions['filter'];

        if ($filter['price'] == 'free') {
            $coinSetting = $this->getSettingService()->get("coin");
            $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
            $priceType   = "RMB";

            if ($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
                $priceType = $coinSetting["price_type"];
            }

            $conditions['price'] = '0.00';
        }

        if ($filter['type'] == 'live') {
            $conditions['type'] = 'live';
        }

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100), 'id');

            if ($filter['currentLevelId'] != 'all') {
                $vipLevelIds               = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($filter['currentLevelId']), 'id');
                $conditions['vipLevelIds'] = array_merge(array($filter['currentLevelId']), $vipLevelIds);
            }
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
        $conditions['status']   = 'published';

        $paginator              = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions),
            20
        );

        if ($orderBy != 'recommendedSeq') {
            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                $orderBy,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        if ($orderBy == 'recommendedSeq') {
            $conditions['recommended'] = 1;
            $recommendCount            = $this->getCourseService()->searchCourseCount($conditions);
            $currentPage               = $request->query->get('page') ? $request->query->get('page') : 1;
            $recommendPage             = intval($recommendCount / 20);
            $recommendLeft             = $recommendCount % 20;

            if ($currentPage <= $recommendPage) {
                $courses = $this->getCourseService()->searchCourses(
                    $conditions,
                    $orderBy,
                    ($currentPage - 1) * 20,
                    20
                );
            } elseif (($recommendPage + 1) == $currentPage) {
                $courses = $this->getCourseService()->searchCourses(
                    $conditions,
                    $orderBy,
                    ($currentPage - 1) * 20,
                    20
                );
                $conditions['recommended'] = 0;
                $coursesTemp               = $this->getCourseService()->searchCourses(
                    $conditions,
                    'createdTime',
                    0,
                    20 - $recommendLeft
                );
                $courses = array_merge($courses, $coursesTemp);
            } else {
                $conditions['recommended'] = 0;
                $courses                   = $this->getCourseService()->searchCourses(
                    $conditions,
                    'createdTime',
                    (20 - $recommendLeft) + ($currentPage - $recommendPage - 2) * 20,
                    20
                );
            }
        }

        $group = $this->getCategoryService()->getGroupByCode('course');

        if (!$categoryArray) {
            $categoryArrayDescription = array();
        } else {
            $categoryArrayDescription = $categoryArray['description'];
            $categoryArrayDescription = strip_tags($categoryArrayDescription, '');
            $categoryArrayDescription = preg_replace("/ /", "", $categoryArrayDescription);
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

        return $this->render('TopxiaWebBundle:Course:explore.html.twig', array(
            'courses'                  => $courses,
            'category'                 => $category,
            'filter'                   => $filter,
            'orderBy'                  => $orderBy,
            'paginator'                => $paginator,
            'consultDisplay'           => true,
            'path'                     => 'course_explore',
            'categoryArray'            => $categoryArray,
            'categoryArrayDescription' => $categoryArrayDescription,
            'categoryParent'           => $categoryParent,
            'levels'                   => $levels,
            'tags'                     => $tags
        ));
    }

    public function classroomAction(Request $request, $category)
    {
        $conditions             = $request->query->all();

        $conditions['status']   = 'published';
        $conditions['showable'] = 1;

        $selectedTag        = '';
        $selectedTagGroupId = '';
        $tags               = array();
        $categoryArray      = array();

        if (!empty($conditions['tag'])) {
            if (!empty($conditions['tag']['tags'])) {
                $tags = $conditions['tag']['tags'];
            }

            if (!empty($conditions['tag']['selectedTag'])) {
                $selectedTag        = $conditions['tag']['selectedTag']['tag'];
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

            $tagOwnerRelations = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType($conditions['tagIds'], 'classroom');
            $classroomIds      = ArrayToolkit::column($tagOwnerRelations, 'ownerId');
            $flag              = array_count_values($classroomIds);

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
        
        $subCategory        = empty($conditions['subCategory']) ? null : $conditions['subCategory'];
        $thirdLevelCategory = empty($conditions['selectedthirdLevelCategory']) ? null : $conditions['selectedthirdLevelCategory'];
        
        if (!empty($conditions['subCategory']) && empty($conditions['selectedthirdLevelCategory'])) {
            $conditions['code'] = $subCategory;
        } elseif (!empty($conditions['selectedthirdLevelCategory']) ) {
            $conditions['code'] = $thirdLevelCategory;
        } else {
            $conditions['code'] = $category;
        }

        if (!empty($conditions['code'])) {
            $categoryArray = $this->getCategoryService()->getCategoryByCode($conditions['code']);

            $conditions['categoryId'] = $categoryArray['id'];
        }

        $category = array(
            'category'           => $category,
            'subCategory'        => $subCategory,
            'thirdLevelCategory' => $thirdLevelCategory,
        );

        unset($conditions['code']);

        if (!isset($conditions['filter'])) {
            $conditions['filter'] = array(
                'price'          => 'all',
                'currentLevelId' => 'all'
            );
        }

        $filter = $conditions['filter'];

        if ($filter['price'] == 'free') {
            $conditions['price'] = '0.00';
        }

        unset($conditions['filter']);
        $levels = array();

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100), 'id');

            if (!$filter['currentLevelId'] != 'all') {
                $vipLevelIds               = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($filter['currentLevelId']), 'id');
                $conditions['vipLevelIds'] = array_merge(array($filter['currentLevelId']), $vipLevelIds);
            }
        }

        $classroomSetting = $this->getSettingService()->get('classroom');

        if (!isset($classroomSetting['explore_default_orderBy'])) {
            $classroomSetting['explore_default_orderBy'] = 'createdTime';
        }

        $orderBy = empty($conditions['orderBy']) ? $classroomSetting['explore_default_orderBy'] : $conditions['orderBy'];
        
        if ($orderBy == 'recommendedSeq') {
            $conditions['recommended'] = 1;
            $orderBy = array($orderBy, 'asc');
        } else {
            $orderBy = array($orderBy, 'desc');
        }

        unset($conditions['orderBy']);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->searchClassroomsCount($conditions),
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
            $categoryArrayDescription = preg_replace("/ /", "", $categoryArrayDescription);
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

        return $this->render("ClassroomBundle:Classroom:explore.html.twig", array(
            'paginator'                => $paginator,
            'classrooms'               => $classrooms,
            'path'                     => 'classroom_explore',
            'category'                 => $category,
            'subCategory'              => $subCategory,
            'categoryArray'            => $categoryArray,
            'categoryArrayDescription' => $categoryArrayDescription,
            'categoryParent'           => $categoryParent,
            'filter'                   => $filter,
            'levels'                   => $levels,
            'orderBy'                  => $orderBy[0],
            'tags'                     => $tags,
            'group'                    => 'classroom'
        ));
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getDiscountService()
    {
        return $this->getServiceKernel()->createService('Discount:Discount.DiscountService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    public function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}