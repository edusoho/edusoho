<?php
namespace Mooc\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\CourseLessonManageController as TopxiaCourseLessonManageController;

class CourseLessonManageController extends TopxiaCourseLessonManageController
{
    public function createTestPaperAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        if ($request->getMethod() == 'POST') {
            $lesson                  = $request->request->all();
            $lesson['type']          = 'testpaper';
            $lesson['courseId']      = $course['id'];
            $lesson['testStartTime'] = isset($lesson['testStartTime']) ? strtotime($lesson['testStartTime']) : 0;

            if (!$lesson['testStartTime']) {
                unset($lesson['testStartTime']);
            }

            $lesson = $this->getCourseService()->createLesson($lesson);
            return $this->render('TopxiaWebBundle:CourseTestpaperManage:list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson
            ));
        }

        $parentId             = $request->query->get('parentId');
        $conditions           = array();
        $conditions['target'] = "course-{$course['id']}";
        $conditions['status'] = 'open';

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime', 'DESC'),
            0,
            1000
        );

        $paperOptions = array();

        foreach ($testpapers as $testpaper) {
            $paperOptions[$testpaper['id']] = $testpaper['name'];
        }

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('TopxiaWebBundle:CourseTestpaperManage:testpaper-modal.html.twig', array(
            'course'       => $course,
            'paperOptions' => $paperOptions,
            'features'     => $features,
            'parentId'     => $parentId
        ));
    }

    public function editTestpaperAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);

        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            if (!empty($fields['testStartTime'])) {
                $fields['testStartTime'] = strtotime($fields['testStartTime']);
            }

            $lesson = $this->getCourseService()->updateLesson($course['id'], $lesson['id'], $fields);
            return $this->render('TopxiaWebBundle:CourseTestpaperManage:list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson
            ));
        }

        $conditions           = array();
        $conditions['target'] = "course-{$course['id']}";
        $conditions['status'] = 'open';

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime', 'DESC'),
            0,
            1000
        );

        $paperOptions = array();

        foreach ($testpapers as $paper) {
            $paperOptions[$paper['id']] = $paper['name'];
        }

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('TopxiaWebBundle:CourseTestpaperManage:testpaper-modal.html.twig', array(
            'course'       => $course,
            'lesson'       => $lesson,
            'paperOptions' => $paperOptions,
            'features'     => $features

        ));
    }
}
