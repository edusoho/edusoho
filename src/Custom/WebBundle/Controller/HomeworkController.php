<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\WebBundle\Controller\CourseBaseController;

class HomeworkController extends CourseBaseController
{
    public function indexAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $id);
        if ($response) {
            return $response;
        }

        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $lessons = ArrayToolkit::index($lessons, 'id');

        $lessonIds = ArrayToolkit::column($lessons, 'id');

        if ($this->isPluginInstalled('Homework')) {
            $homeworks = $this->getHomeworkService()->findHomeworksByCourseIdAndLessonIds($course['id'], $lessonIds);
        }

        $homeworkResults = $this->getHomeworkService()->getResultByCourseIdAndUserId($id, $user['id']);
        $homeworkItemsResults = $this->getHomeworkService()->findItemResultsbyUserId($user['id']);

        return $this->render("CustomWebBundle:Homework:index.html.twig", array(
            'course' => $course,
            'member' => $member,
            'lessons' => $lessons,
            'homeworks' => empty($homeworks) ? array() : $homeworks,
            'homeworkResults' => empty($homeworkResults) ? array() : $homeworkResults,
            'homeworkItemsResults' => empty($homeworkItemsResults) ? array() : $homeworkItemsResults,
            'now' => time(),
        ));
    }

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Custom:Homework.HomeworkService');
    }
}