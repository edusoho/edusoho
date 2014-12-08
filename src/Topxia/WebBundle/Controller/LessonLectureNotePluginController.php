<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class LessonLectureNotePluginController extends BaseController
{
    public function initAction (Request $request)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($request->query->get('courseId'));
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $request->query->get('lessonId'));

        $lessonLectureNotes = $this->getLectureNoteService()->findLessonLectureNotes($lesson['id']);
        $essayIds = ArrayToolkit::column($lessonLectureNotes,'essayId');
        array_filter($essayIds);
        $essays = array();$essayContentItems = array();
        foreach ($essayIds as $id) {
            $essays[] = $this->getEssayService()->getEssay($id);
            $essayContentItems[] = $this->getEssayContentService()->getEssayItems($id);
        }

        $essayMaterialIds = ArrayToolkit::column($lessonLectureNotes,'essayMaterialId');
        array_filter($essayMaterialIds);
        $essayMaterials = $this->getArticleMaterialService()->getArticleMaterialsByIds($essayMaterialIds);

        return $this->render('TopxiaWebBundle:LessonLectureNotePlugin:index.html.twig',array(
            'lessonLectureNotes' => $lessonLectureNotes,
            'essays' => $essays,
            'essayContentItems' => $essayContentItems,
            'essayMaterials' => $essayMaterials,
            'course' => $course,
            'lesson' => $lesson,
        ));
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
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

}