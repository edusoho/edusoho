<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ClassroomToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class CourseController extends BaseController
{
    public function pickAction($classroomId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);

        $conditions = [
            'status' => 'published',
            'parentId' => 0,
            'types' => [CourseSetService::NORMAL_TYPE, CourseSetService::LIVE_TYPE],
        ];

        $activeCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        if (!empty($activeCourses)) {
            $conditions['excludeIds'] = ArrayToolkit::column($activeCourses, 'parentCourseSetId');
        }

        $user = $this->getCurrentUser();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $conditions['creator'] = $user['id'];
        }

        $conditions = $this->convertS2b2cConditions($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );

        $courseSets = $this->searchCourseSetWithCourses(
            $conditions,
            ['updatedTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        $template = 'classroom-manage/course/course-pick-modal.html.twig';
        $page = $this->get('request')->query->get('page');
        if (!empty($page)) {
            $template = 'course/course-select-list.html.twig';
        }

        return $this->render(
            $template,
            [
                'users' => $users,
                'courseSets' => $courseSets,
                'classroomId' => $classroomId,
                'paginator' => $paginator,
                'type' => 'ajax_pagination',
            ]
        );
    }

    public function listAction(Request $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        //检查权限
        if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
            $classroomName = $this->setting('classroom.name', '班级');

            return $this->createMessageResponse(
                'info',
                "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服",
                '',
                3,
                $this->generateUrl('homepage')
            );
        }

        //课程数据 ?title=
        $title = trim($request->get('title', ''));
        $courses = $this->getClassroomService()->findSortedCoursesByClassroomIdAndTitle($classroomId, $title);
        $currentUser = $this->getCurrentUser();
        $teachers = [];
        $courseMembers = $this->getCourseMemberService()->findCourseMembersByUserIdAndCourseIds($currentUser['id'], array_column($courses, 'id'));
        $tasks = $this->getTaskService()->findTasksByIds(array_column($courseMembers, 'lastLearnTaskId'));
        $courseMembers = array_column($courseMembers, null, 'courseId');
        $tasks = array_column($tasks, null, 'id');
        foreach ($courses as &$course) {
            $member = $courseMembers[$course['id']] ?? [];
            $course['lastLearnTask'] = empty($tasks[$member['lastLearnTaskId']]) ? null : [
                'id' => $member['lastLearnTaskId'],
                'number' => $tasks[$member['lastLearnTaskId']]['number'],
                'title' => $tasks[$member['lastLearnTaskId']]['title'],
            ];
            $course['teachers'] = empty($course['teacherIds']) ? [] : $this->getUserService()->findUsersByIds(
                $course['teacherIds']
            );
            $teachers[$course['id']] = $course['teachers'];
            if ($course['isFree']) {
                $course['originPrice'] = '0.00';
            }
        }

        //是否有管理课程的权限
        $canManageClassroom = $this->getClassroomService()->canManageClassroom($classroomId);
        $previewAs = $request->query->get('previewAs', '');
        if (empty($previewAs) || !$canManageClassroom) {
            $previewAs = '';
        }
        $member = $currentUser['id'] ? $this->getClassroomService()->getClassroomMember($classroom['id'], $currentUser['id']) : null;
        $member = $this->previewAsMember($previewAs, $member, $classroom);
        if (!$member || $member['locked']) {
            $product = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
            $goods = $this->getGoodsService()->getGoodsByProductId($product['id']);

            return $this->redirect($this->generateUrl('goods_show', ['id' => $goods['id'], 'preview' => 'guest' == $previewAs ? 1 : 0]));
        }

        //是否注册了vip
        if ($this->isPluginInstalled('Vip')) {
            $member['access'] = $this->getClassroomService()->canLearnClassroom($classroomId);
        }

        $layout = 'classroom/layout.html.twig';
        $isCourseMember = false;
        if ($member && !$member['locked']) {
            $isCourseMember = true;
            $layout = 'classroom/join-layout.html.twig';
        }

        $classroomDescription = $classroom['about'];
        $classroomDescription = strip_tags($classroomDescription, '');
        $classroomDescription = preg_replace('/ /', '', $classroomDescription);

        return $this->render(
            'classroom/course/list.html.twig',
            [
                'classroom' => $classroom,
                'member' => $member,
                'teachers' => $teachers,
                'courses' => $courses,
                'courseMembers' => $courseMembers,
                'layout' => $layout,
                'classroomDescription' => $classroomDescription,
                'isCourseMember' => $isCourseMember,
            ]
        );
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return LevelService
     */
    protected function getVipLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    /**
     * @return VipRightService
     */
    protected function getVipRightService()
    {
        return $this->createService('VipPlugin:Marketing:VipRightService');
    }

    public function searchAction(Request $request, $classroomId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $key = $request->request->get('key');

        $activeCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        $excludeIds = ArrayToolkit::column($activeCourses, 'parentCourseSetId');

        $conditions = [
            'title' => "%{$key}%",
            'status' => 'published',
            'parentId' => 0,
            'excludeIds' => $excludeIds,
            'excludeTypes' => ['reservation'],
        ];

        $user = $this->getCurrentUser();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $conditions['creator'] = $user['id'];
        }

        $conditions = $this->convertS2b2cConditions($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            5
        );

        $courseSets = $this->searchCourseSetWithCourses(
            $conditions,
            ['updatedTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUsers($courseSets);

        return $this->render(
            'course/course-select-list.html.twig',
            [
                'users' => $users,
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'classroomId' => $classroomId,
                'type' => 'ajax_pagination',
            ]
        );
    }

    protected function getUsers($courseSets)
    {
        $userIds = [];
        foreach ($courseSets as &$courseSet) {
            // $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course', 'ownerId' => $course['id']));
            if (!empty($courseSet['tags'])) {
                $tags = $this->getTagService()->findTagsByIds($courseSet['tags']);

                $courseSet['tags'] = ArrayToolkit::column($tags, 'id');
            }
            $userIds = array_merge($userIds, [$courseSet['creator']]);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        if (!empty($users)) {
            $users = ArrayToolkit::index($users, 'id');
        }

        return $users;
    }

    /**
     * @param string $previewAs
     * @param array  $member
     * @param array  $classroom
     *
     * @return array
     */
    private function previewAsMember($previewAs, $member, $classroom)
    {
        $user = $this->getCurrentUser();

        if (in_array($previewAs, ['guest', 'auditor', 'member'], true)) {
            if ('guest' === $previewAs) {
                return [];
            }

            $deadline = ClassroomToolkit::buildMemberDeadline([
                'expiryMode' => $classroom['expiryMode'],
                'expiryValue' => $classroom['expiryValue'],
            ]);

            $member = [
                'id' => 0,
                'classroomId' => $classroom['id'],
                'userId' => $user['id'],
                'orderId' => 0,
                'levelId' => 0,
                'noteNum' => 0,
                'threadNum' => 0,
                'remark' => '',
                'role' => ['auditor'],
                'locked' => 0,
                'createdTime' => 0,
                'deadline' => $deadline,
            ];

            if ('member' === $previewAs) {
                $member['role'] = ['member'];
            }
        }

        return $member;
    }

    private function searchCourseSetWithCourses($conditions, $orderbys, $start, $limit)
    {
        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, $orderbys, $start, $limit);

        if (empty($courseSets)) {
            return [];
        }

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array_keys($courseSets));
        if (!empty($courses)) {
            foreach ($courses as $course) {
                if ('published' != $course['status']) {
                    continue;
                }

                if (empty($courseSets[$course['courseSetId']]['courses'])) {
                    $courseSets[$course['courseSetId']]['courses'] = [$course];
                } else {
                    $courseSets[$course['courseSetId']]['courses'][] = $course;
                }
            }
        }

        return array_values($courseSets);
    }

    private function convertS2b2cConditions($conditions)
    {
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        if ($s2b2cConfig['enabled']) {
            $conditions['platform'] = 'self';
        }

        return $conditions;
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return TagService
     */
    private function getTagService()
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

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
