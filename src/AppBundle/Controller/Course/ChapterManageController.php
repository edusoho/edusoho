<?php

namespace AppBundle\Controller\Course;

use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class ChapterManageController extends BaseController
{
    public function createAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $type = $request->query->get('type');
        $parentId = $request->query->get('parentId');
        $type = in_array($type, array('chapter', 'unit', 'lesson')) ? $type : 'chapter';

        if ($request->getMethod() == 'POST') {
            $chapter = $request->request->all();
            $chapter['courseId'] = $course['id'];
            $chapter = $this->getCourseService()->createChapter($chapter);

            return $this->render('course-manage/chapter/list-item.html.twig', array(
                'course' => $course,
                'chapter' => $chapter,
            ));
        }

        return $this->render('course-manage/chapter/chapter-modal.html.twig', array(
            'course' => $course,
            'type' => $type,
            'parentId' => $parentId,
        ));
    }

    public function editAction(Request $request, $courseId, $chapterId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $chapter = $this->getCourseService()->getChapter($courseId, $chapterId);

        if (empty($chapter)) {
            throw $this->createNotFoundException("Chapter#{$chapterId} Not Found");
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['courseId'] = $course['id'];
            $chapter = $this->getCourseService()->updateChapter($courseId, $chapterId, $fields);

            return $this->render('course-manage/chapter/list-item.html.twig', array(
                'course' => $course,
                'chapter' => $chapter,
            ));
        }

        return $this->render('course-manage/chapter/chapter-modal.html.twig', array(
            'course' => $course,
            'chapter' => $chapter,
            'type' => $chapter['type'],
        ));
    }

    public function deleteAction(Request $request, $courseId, $chapterId)
    {
        $this->getCourseService()->deleteChapter($courseId, $chapterId);

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
