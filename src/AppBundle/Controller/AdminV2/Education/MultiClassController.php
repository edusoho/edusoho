<?php

namespace AppBundle\Controller\AdminV2\Education;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ReportService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;
use Symfony\Component\HttpFoundation\Request;

class MultiClassController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/teach/multi_class/index.html.twig', [
        ]);
    }

    public function overviewAction(Request $request, $multiClassId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $course = $this->getCourseService()->tryManageCourse($multiClass['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $summary = $this->getReportService()->summary($course['id']);

        return $this->render(
            'admin-v2/teach/multi-class/overview/overview.html.twig',
            [
                'summary' => $summary,
                'courseSet' => $courseSet,
                'course' => $course,
            ]
        );
    }

    public function inspectionAction(Request $request)
    {
        return $this->render('admin-v2/education/inspection/index.html.twig', [
        ]);
    }

    public function settingAction(Request $request)
    {
        return $this->render('admin-v2/education/setting/index.html.twig', [
        ]);
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ReportService
     */
    protected function getReportService()
    {
        return $this->createService('Course:ReportService');
    }
}
