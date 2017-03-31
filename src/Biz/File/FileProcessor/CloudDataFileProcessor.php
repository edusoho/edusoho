<?php

namespace Biz\File\FileProcessor;

use AppBundle\Common\ArrayToolkit;

class CloudDataFileProcessor extends BaseFileProcessor
{
    public function getCourseCloudFileInfo($request)
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
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
