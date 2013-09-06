<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\CourseType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;

class CourseController extends BaseController
{
    public function exploreAction(Request $request, $category)
    {
        if (!empty($category)) {
            if (ctype_digit((string) $category)) {
                $category = $this->getCategoryService()->getCategory($category);
            } else {
                $category = $this->getCategoryService()->getCategoryByCode($category);
            }

            if (empty($category)) {
                throw $this->createNotFoundException();
            }
        } else {
            $category = array('id' => null);
        }

        $sort = $request->query->get('sort', 'popular');

        $conditions = array(
            'status' => 'published',
            'categoryId' => $category['id'],
            'recommended' => ($sort == 'recommended') ? 1 : null
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            , 10
        );


        $courses = $this->getCourseService()->searchCourses(
            $conditions, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findGroupRootCategories('course');

        return $this->render('TopxiaWebBundle:Course:explore.html.twig', array(
            'courses' => $courses,
            'category' => $category,
            'sort' => $sort,
            'paginator' => $paginator,
            'categories' => $categories,
        ));
    }

    public function infoAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $category = $this->getCategoryService()->getCategory($course['categoryId']);
        $tags = $this->getTagService()->findTagsByIds($course['tags']);
        return $this->render('TopxiaWebBundle:Course:info-modal.html.twig', array(
            'course' => $course,
            'category' => $category,
            'tags' => $tags,
        ));
    }

    public function teacherInfoAction(Request $request, $courseId, $id)
    {
        $currentUser = $this->getCurrentUser();

        $course = $this->getCourseService()->getCourse($courseId);
        $user = $this->getUserService()->getUser($id);
        $profile = $this->getUserService()->getUserProfile($id);

        $isFollowing = $this->getUserService()->isFollowed($currentUser->id, $user['id']);

        return $this->render('TopxiaWebBundle:Course:teacher-info-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'isFollowing' => $isFollowing,
        ));
    }

    public function membersAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryTakeCourse($id);

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->getCourseStudentCount($course['id']),
            6
        );

        $students = $this->getCourseService()->findCourseStudents(
            $course['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $followingIds = $this->getUserService()->filterFollowingIds($this->getCurrentUser()->id, $studentUserIds);

        $progresses = array();
        foreach ($students as $student) {
            $progresses[$student['userId']] = $this->calculateUserLearnProgress($course, $student);
        }

        return $this->render('TopxiaWebBundle:Course:members-modal.html.twig', array(
            'course' => $course,
            'students' => $students,
            'users'=>$users,
            'progresses' => $progresses,
            'followingIds' => $followingIds,
            'paginator' => $paginator,
            'canManage' => $this->getCourseService()->canManageCourse($course),
        ));
    }

    /**
     * 如果用户已购买了此课程，或者用户是该课程的教师，则显示课程的Dashboard界面。
     * 如果用户未购买该课程，那么显示课程的营销界面。
     */
    public function showAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        if (empty($course)) {
            throw $this->createNotFoundException();
        }
        
        $user = $this->getCurrentUser();

        $items = $this->getCourseService()->getCourseItems($course['id']);

        $member = $user ? $this->getCourseService()->getCourseMember($course['id'], $user['id']) : null;
        $member = $this->previewAsMember($request->query->get('previewAs'), $member, $course);

        if ($member) {
            $learnStatuses = $this->getCourseService()->getUserLearnLessonStatuses($user['id'], $course['id']);

            return $this->render("TopxiaWebBundle:Course:dashboard.html.twig", array(
                'course' => $course,
                'member' => $member,
                'items' => $items,
                'learnStatuses' => $learnStatuses,
            ));
        }

        $groupedItems = $this->groupCourseItems($items);
        $hasFavorited = $this->getCourseService()->hasFavoritedCourse($course['id']);

        return $this->render("TopxiaWebBundle:Course:show.html.twig", array(
            'course' => $course,
            'member' => $member,
            'groupedItems' => $groupedItems,
            'hasFavorited' => $hasFavorited,
        ));

    }

    private function previewAsMember($as, $member, $course)
    {
        $user = $this->getCurrentUser();
        if (empty($user->id)) {
            return null;
        }
        // var_dump($user);


        if (in_array($as, array('member', 'guest'))) {
            if ($this->get('security.context')->isGranted('ROLE_ADMIN') and empty($member)) {
                $member = array(
                    'id' => 0,
                    'courseId' => $course['id'],
                    'userId' => $user['id'],
                    'learnedNum' => 0,
                    'isLearned' => 0,
                    'seq' => 0,
                    'isVisible' => 0,
                    'role' => 'teacher',
                    'createdTime' => time()
                );
            }

            if (empty($member) or $member['role'] != 'teacher') {
                return $member;
            }

            if ($as == 'member') {
                $member['role'] = 'student';
            } else {
                $member = null;
            }
        }

        return $member;
    }

    private function groupCourseItems($items)
    {
        $grouped = array();

        $list = array();
        foreach ($items as $id => $item) {
            if ($item['itemType'] == 'chapter') {
                if (!empty($list)) {
                    $grouped[] = array('type' => 'list', 'data' => $list);
                    $list = array();
                }
                $grouped[] = array('type' => 'chapter', 'data' => $item);
            } else {
                $list[] = $item;
            }
        }

        if (!empty($list)) {
            $grouped[] = array('type' => 'list', 'data' => $list);
        }

        return $grouped;
    }

    private function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100) . '%';

        return array (
            'percent' => $percent,
            'number' => $member['learnedNum'],
            'total' => $course['lessonNum']
        );
    }
    
    public function favoriteAction(Request $request, $id)
    {
        $this->getCourseService()->favoriteCourse($id);
        return $this->createJsonResponse(true);
    }

    public function unfavoriteAction(Request $request, $id)
    {
        $this->getCourseService()->unfavoriteCourse($id);
        return $this->createJsonResponse(true);
    }

	public function createAction(Request $request)
	{
		$form = $this->createCourseForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $course = $form->getData();
                $course = $this->getCourseService()->createCourse($course);
                return $this->redirect($this->generateUrl('course_manage', array('id' => $course['id'])));
            }
        }

		return $this->render('TopxiaWebBundle:Course:create.html.twig', array(
			'form' => $form->createView()
		));
	}

    public function joinAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($id);

        $this->getOrderService()->createOrder(array('courseId' => $course['id']));
        $this->getCourseService()->joinCourse($user['id'], $course['id']);

        return $this->createJsonResponse(true);
    }

    public function exitAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->tryTakeCourse($id);
        $this->getCourseService()->exitCourse($user['id'], $course['id']);
        return $this->createJsonResponse(true);
    }

    public function learnAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryTakeCourse($id);
        return $this->render('TopxiaWebBundle:Course:learn.html.twig', array(
            'course' => $course,
        ));
    }

    /**
     * Block Actions
     */

    public function headerAction($course, $manage = false)
    {
        $user = $this->getCurrentUser();

        $users = empty($course['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($course['teacherIds']);

        return $this->render('TopxiaWebBundle:Course:header.html.twig', array(
            'course' => $course,
            'canManage' => $this->getCourseService()->canManageCourse($course),
            'member' => $this->getCourseService()->getCourseMember($course['id'], $user['id']),
            'users' => $users,
            'manage' => $manage,
        ));
    }

    public function teachersBlockAction($course)
    {
        $users = $this->getUserService()->findUsersByIds($course['teacherIds']);
        $profiles = $this->getUserService()->findUserProfilesByIds($course['teacherIds']);

        return $this->render('TopxiaWebBundle:Course:teachers-block.html.twig', array(
            'course' => $course,
            'users' => $users,
            'profiles' => $profiles,
        ));
    }

    public function progressBlockAction($course)
    {
        $user = $this->getCurrentUser();

        $member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);
        $nextLearnLesson = $this->getCourseService()->getUserNextLearnLesson($user['id'], $course['id']);

        $progress = $this->calculateUserLearnProgress($course, $member);
        return $this->render('TopxiaWebBundle:Course:progress-block.html.twig', array(
            'course' => $course,
            'member' => $member,
            'nextLearnLesson' => $nextLearnLesson,
            'progress'  => $progress,
        ));
    }

    public function latestMembersBlockAction($course, $count = 10)
    {
        $students = $this->getCourseService()->findCourseStudents($course['id'], 0, 12);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($students, 'userId'));
        return $this->render('TopxiaWebBundle:Course:latest-members-block.html.twig', array(
            'students' => $students,
            'users' => $users,
        ));
    }

    public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
    {
        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:Course:courses-block-{$view}.html.twig", array(
            'courses' => $courses,
            'users' => $users,
            'mode' => $mode,
        ));
    }

    private function createCourseForm()
    {
        return $this->createNamedFormBuilder('course')
            ->add('title', 'text')
            ->getForm();
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

}