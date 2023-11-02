<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;

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

            return $this->render('lesson-manage/chapter/item.html.twig', [
                'course' => $course,
                'chapter' => $chapter,
            ]);
        }

        $type = $request->query->get('type', 'chapter');

        return $this->render('lesson-manage/chapter/modal.html.twig', [
            'course' => $course,
            'type' => $type,
            'chapter' => $chapter,
        ]);
    }

    protected function editor($chapterId, $courseId, $fields)
    {
        return $this->getCourseService()->updateChapter($courseId, $chapterId, ['title' => $fields['title']]);
    }

    protected function create($chapter, $courseId)
    {
        $chapter['courseId'] = $courseId;

        return $this->getCourseService()->createChapter($chapter);
    }

    public function deleteAction(Request $request, $courseId, $chapterId)
    {
        $this->getCourseService()->deleteChapter($courseId, $chapterId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function publishAction(Request $request, $courseId, $chapterId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseService()->publishChapter($chapterId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function unpublishAction(Request $request, $courseId, $chapterId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseService()->unpublishChapter($chapterId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function acquireFilterChapterAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $chapters = $this->getCourseService()->searchCourseChapters(
            ['courseId' => $course['id']],
            ['seq' => 'ASC'],
            0,
            PHP_INT_MAX,
            ['id', 'title', 'type']
        );

        $lessonTree = $this->getCourseService()->getLessonTree($chapters);

        return $this->createJsonResponse($lessonTree);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
