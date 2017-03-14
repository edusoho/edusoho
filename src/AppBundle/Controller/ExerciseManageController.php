<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ExerciseManageController extends BaseController
{
    public function buildCheckAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($course['id']);

        $fields = $request->request->all();

        $fields['courseSetId'] = $course['courseSetId'];
        $fields['excludeUnvalidatedMaterial'] = 1;

        $result = $this->getTestpaperService()->canBuildTestpaper('exercise', $fields);

        return $this->createJsonResponse($result);
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
