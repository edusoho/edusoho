<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class LessonMaterialPluginController extends BaseController
{
    public function initAction (Request $request)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($request->query->get('courseId'));
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $request->query->get('lessonId'));

        $lessonMaterials = $this->getLectureNoteService()->findLectureNotesByLessonIdAndType($lesson['id'], 'metarial');
        $categoryId = '3';

        return $this->render('TopxiaWebBundle:LessonMaterialPlugin:index.html.twig',array(
            'lessonMaterials' => $lessonMaterials,
            'course' => $course,
            'lesson' => $lesson,
            'categoryId' => $categoryId,
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getLectureNoteService()
    {
        return $this->getServiceKernel()->createService('Custom:LectureNote.LectureNoteService');
    }

    private function getArticleMaterialService()
    {
        return $this->getServiceKernel()->createService('ArticleMaterial.ArticleMaterialService');
    }

    private function getEssayService()
    {
        return $this->getServiceKernel()->createService('Essay.EssayService');
    }

    private function getEssayContentService()
    {
        return $this->getServiceKernel()->createService('EssayContent.EssayContentService');
    }
}