<?php

namespace AppBundle\Controller\Course;

use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class ChapterManageController extends BaseController
{
    public function manageAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $chapterId = $request->query->get('chapterId', 0);
        $chapter = $this->getCourseService()->getChapter($courseId, $chapterId);

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $chapter = empty($chapter) ? $this->create($fields, $courseId) : $this->editor($chapterId, $courseId, $fields);

            return $this->render('lesson-manage/chapter/item.html.twig', array(
                'course' => $course,
                'chapter' => $chapter,
            ));
        }

        $type = $request->query->get('type', 'chapter');

        return $this->render('lesson-manage/chapter/modal.html.twig', array(
            'course' => $course,
            'type' => $type,
            'chapter' => $chapter,
        ));
    }

    protected function editor($chapterId, $courseId, $fields)
    {
        return $this->getCourseService()->updateChapter($courseId, $chapterId, array('title' => $fields['title']));
    }

    protected function create($chapter, $courseId)
    {
        $chapter['courseId'] = $courseId;

        return $this->getCourseService()->createChapter($chapter);
    }

    public function deleteAction(Request $request, $courseId, $chapterId)
    {
        $this->getCourseService()->deleteChapter($courseId, $chapterId);

        return $this->createJsonResponse(array('success' => true));
    }

    public function publishAction(Request $request, $courseId, $chapterId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseService()->publishChapter($chapterId);

        return $this->createJsonResponse(array('success' => true));
    }

    public function unpublishAction(Request $request, $courseId, $chapterId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseService()->unpublishChapter($chapterId);

        return $this->createJsonResponse(array('success' => true));
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
