<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ExerciseManageController extends BaseController
{
    public function buildCheckAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $fields = $request->request->all();

        $fields['courseSetId'] = $course['courseSetId'];
        $fields['excludeUnvalidatedMaterial'] = 1;

        $result = $this->getTestpaperService()->canBuildTestpaper('exercise', $fields);

        $status = false;
        if ($result['status'] == 'yes') {
            $status = true;
        }

        return $this->createJsonResponse($status);
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
