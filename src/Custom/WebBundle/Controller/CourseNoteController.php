<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\Course\NoteController as NoteBaseController;

/**
 * 作业笔记控制器.
**/
class CourseNoteController extends NoteBaseController
{
    public function indexAction(Request $request, $courseId)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $courseId);
        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        return $this->render('CustomWebBundle:CourseNote:index.html.twig', array(
            'course' => $course,
            'member' => $member,
            'filters' => $this->getNoteSearchFilters($request),
            'lessons' => $lessons
        ));
    }
}