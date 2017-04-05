<?php

namespace AppBundle\Controller\Callback\CourseLive\Resource;

use AppBundle\Controller\Callback\CourseLive\BaseProvider;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class CourseCloudFiles extends BaseProvider
{
    public function get(Request $request)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 100);
        $name = $request->query->get('name', '');
        $courseId = $request->query->get('courseId');

        $course = $this->getCourseService()->getCourse($courseId);
        $conditions = array('targetId' => $course['courseSetId'], 'storage' => 'cloud');
        if ($name) {
            $conditions['filename'] = $name;
        }

        $sourceCourseFiles = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );
        $cloudFiles = $this->buildNeedCloudFileFields($sourceCourseFiles);

        return $cloudFiles;
    }

    protected function buildNeedCloudFileFields($sourceCourseFiles)
    {
        $cloudFiles = array();
        $filter = array( 'type' => '', 'status' => '', 'globalId' => 0, 'filename' => '');

        foreach ($sourceCourseFiles as $sourceCourseFile) {
            $cloudFile = ArrayToolkit::filter($filter, $sourceCourseFile);

            $cloudFile['mediaId'] = $cloudFile['globalId'];
            unset($cloudFile['globalId']);

            $cloudFile['name'] = $cloudFile['filename'];
            unset($cloudFile['filename']);

            $cloudFiles['data'][] = $cloudFile;
        }
        $cloudFiles['total'] = count($sourceCourseFiles);
        return $cloudFiles;
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
