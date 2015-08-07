<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\CourseType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\WebBundle\Controller\CourseController as CourseBaseController;

class CourseController extends CourseBaseController
{
	public function createAction(Request $request)
	{
		$user = $this->getUserService()->getCurrentUser();
		$userProfile = $this->getUserService()->getUserProfile($user['id']);

		$isLive = $request->query->get('flag');
		$type = ($isLive == "isLive") ? 'live' : 'normal';

		if($isLive == "isLive"){
			$type = 'live';
		}elseif($isLive == "periodic"){
			$type = 'periodic';
		}else{
			$type = 'normal';
		}

		if ($type == 'live') {

			$courseSetting = $this->setting('course', array());

			if (!empty($courseSetting['live_course_enabled'])) {
				$client = LiveClientFactory::createClient();
				$capacity = $client->getCapacity();
			} else {
				$capacity = array();
			}

			if (empty($courseSetting['live_course_enabled'])) {
				return $this->createMessageResponse('info', '请前往后台开启直播,尝试创建！');
			}

			if (empty($capacity['capacity']) && !empty($courseSetting['live_course_enabled'])) {
				return $this->createMessageResponse('info', '请联系EduSoho官方购买直播教室，然后才能开启直播功能！');
			}
		}

		if (false === $this->get('security.context')->isGranted('ROLE_TEACHER')) {
			throw $this->createAccessDeniedException();
		}

		if ($request->getMethod() == 'POST') {
			$course = $request->request->all();
			$course = $this->getCourseService()->createCourse($course);
			return $this->redirect($this->generateUrl('course_manage', array('id' => $course['id'])));
		}

		return $this->render('CustomWebBundle:Course:create.html.twig', array(
			'userProfile'=>$userProfile,
			'type'=>$type
		));
	}
    public function showAction(Request $request, $id)
    {
        list ($course, $member) = $this->buildCourseLayoutData($request, $id);
        if(empty($member)) {
            $user = $this->getCurrentUser();
            $member = $this->getCourseService()->becomeStudentByClassroomJoined($id, $user->id);
            if(isset($member["id"])) {
                $course['studentNum'] ++ ;
            }
        }

        $this->getCourseService()->hitCourse($id);
            $items = $this->getCourseService()->getCourseItems($course['id']);

        return $this->render("CustomWebBundle:Course:{$course['type']}-show.html.twig", array(
            'course' => $course,
            'member' => $member,
            'items' => $items,
        ));

    }

    public function nextRoundAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        return $this->render('CustomWebBundle:Course:next-round.html.twig', array(
            'course' => $course,
        ));
    }

    public function roundingAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $conditions = $request->request->all();
        $course['startTime'] = strtotime($conditions['startTime']);
        $course['endTime'] = strtotime($conditions['endTime']);

        $this->getNextRoundService()->rounding($course);

        return $this->redirect($this->generateUrl('my_teaching_courses'));
    }

	public function exploreAction(Request $request, $category)
	{
		$conditions = $request->query->all();

		$conditions['code'] = $category;
		if (!empty($conditions['code'])) {
			$categoryArray = $this->getCategoryService()->getCategoryByCode($conditions['code']);
			$childrenIds = $this->getCategoryService()->findCategoryChildrenIds($categoryArray['id']);
			$categoryIds = array_merge($childrenIds, array($categoryArray['id']));
			$conditions['categoryIds'] = $categoryIds;
		}
		unset($conditions['code']);

		if(!isset($conditions['fliter'])){
			$conditions['fliter'] ='all';
		} elseif ($conditions['fliter'] == 'free') {
			$coinSetting = $this->getSettingService()->get("coin");
			$coinEnable = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
			$priceType = "RMB";
			if ($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
				$priceType = $coinSetting["price_type"];
			}

			if($priceType == 'RMB'){
				$conditions['price'] = '0.00';
			} else {
				$conditions['coinPrice'] = '0.00';
			}
		} elseif ($conditions['fliter'] == 'live'){
			$conditions['type'] = 'live';
		}
		$fliter = $conditions['fliter'];
		unset($conditions['fliter']);

		$courseSetting = $this->getSettingService()->get('course', array());
		if (!isset($courseSetting['explore_default_orderBy'])) {
			$courseSetting['explore_default_orderBy'] = 'latest';
		}
		$orderBy = $courseSetting['explore_default_orderBy'];
		$orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
		unset($conditions['orderBy']);

		$conditions['recommended'] = ($orderBy == 'recommendedSeq') ? 1 : null;

		$conditions['parentId'] = 0;
		$conditions['status'] = 'published';
		$paginator = new Paginator(
			$this->get('request'),
			$this->getCourseService()->searchCourseCount($conditions),
			12
		);
		$courses = $this->getCourseService()->searchCourses(
			$conditions,
			$orderBy,
			$paginator->getOffsetCount(),
			$paginator->getPerPageCount()
		);
		$group = $this->getCategoryService()->getGroupByCode('course');
		if (empty($group)) {
			$categories = array();
		} else {
			$categories = $this->getCategoryService()->getCategoryTree($group['id']);
		}

		return $this->render('CustomWebBundle:Course:explore.html.twig', array(
			'courses' => $courses,
			'category' => $category,
			'fliter' => $fliter,
			'orderBy' => $orderBy,
			'paginator' => $paginator,
			'categories' => $categories,
			'consultDisplay' => true,
			'path' => 'course_explore'

		));
	}

	public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
	{
		$userIds = array();
		foreach ($courses as $key => $course) {
			$userIds = array_merge($userIds, $course['teacherIds']);

			$classroomIds=$this->getClassroomService()->findClassroomIdsByCourseId($course['id']);

			$courses[$key]['classroomCount']=count($classroomIds);

			if(count($classroomIds)>0){
				$classroom=$this->getClassroomService()->getClassroom($classroomIds[0]);
				$courses[$key]['classroom']=$classroom;
			}
		}
		
		$users = $this->getUserService()->findUsersByIds($userIds);
		
		return $this->render("CustomWebBundle:Course:courses-block-{$view}.html.twig", array(
			'courses' => $courses,
			'users' => $users,
			'classroomIds'=>$classroomIds,
			'mode' => $mode,
		));
	}

    protected function getNextRoundService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.NextRoundService');
    }

}