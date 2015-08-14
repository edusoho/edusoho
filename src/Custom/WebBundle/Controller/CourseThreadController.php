<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\CourseType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\WebBundle\Controller\CourseThreadController as CourseThreadBaseController;

class CourseThreadController extends CourseThreadBaseController
{
    public function indexAction(Request $request, $id)
    {
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $id);
        if ($response) {
            return $response;
        }

        $filters = $this->getThreadSearchFilters($request);
        $conditions = $this->convertFiltersToConditions($course, $filters);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            $filters['sort'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        foreach ($threads as $key => $thread) {
            $threads[$key]['sticky'] = $thread['isStick'];
            $threads[$key]['nice'] = $thread['isElite'];
            $threads[$key]['lastPostTime'] = $thread['latestPostTime'];
            $threads[$key]['lastPostUserId'] = $thread['latestPostUserId'];
        }

        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($threads, 'lessonId'));
        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'latestPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("CustomWebBundle:CourseThread:index.html.twig", array(
            'course' => $course,
            'member' => $member,
            'threads' => $threads,
            'users' => $users,
            'paginator' => $paginator,
            'filters' => $filters,
            'lessons'=>$lessons,
            'target' => array('type'=>'course','id'=>$id)
        ));
    }
}