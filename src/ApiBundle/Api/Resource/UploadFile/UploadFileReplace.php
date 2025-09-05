<?php

namespace ApiBundle\Api\Resource\UploadFile;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Biz\File\UploadFileException;
use Biz\Task\Service\TaskService;

class UploadFileReplace extends AbstractResource
{
    /**
     * @Access(permissions="admin_v2_cloud_resource")
     */
    public function add(ApiRequest $request, $fileId)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, ['fileId', 'courseSetIds'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $file = $this->getUploadFileService()->getFile($fileId);
        if (empty($file)) {
            throw UploadFileException::NOTFOUND_FILE();
        }
        $targetFile = $this->getUploadFileService()->getFile($params['fileId']);
        if (empty($targetFile)) {
            throw UploadFileException::NOTFOUND_FILE();
        }
        $this->replace($file, $targetFile, $params['courseSetIds']);

        return ['ok' => true];
    }

    private function replace($file, $targetFile, $courseSetIds)
    {
        $courseMaterials = $this->getCourseMaterialService()->searchMaterials(['fileId' => $file['id'], 'type' => 'course', 'excludeLessonId' => 0, 'courseSetIds' => $courseSetIds], [], 0, PHP_INT_MAX);
        if (empty($courseMaterials)) {
            return;
        }
        $copyCourseMaterials = $this->getCourseMaterialService()->searchMaterials(['fileId' => $file['id'], 'type' => 'course', 'excludeLessonId' => 0, 'copyIds' => array_column($courseMaterials, 'id')], [], 0, PHP_INT_MAX);
        $courseMaterials = array_merge($courseMaterials, $copyCourseMaterials);
        $updateMaterials = [];
        foreach ($courseMaterials as $courseMaterial) {
            $updateMaterials[$courseMaterial['id']] = [
                'title' => $targetFile['filename'],
                'fileId' => $targetFile['id'],
                'fileSize' => $targetFile['fileSize'],
                'userId' => $this->getCurrentUser()->getId(),
            ];
        }
        $this->getCourseMaterialService()->batchUpdateMaterials($updateMaterials);
        $this->getUploadFileService()->updateUsedCount($file['id']);
        $this->getUploadFileService()->updateUsedCount($targetFile['id']);
        $this->getTaskService()->batchUpdateMediaByActivityIds(array_column($courseMaterials, 'lessonId'), [
            'id' => $targetFile['id'],
            'name' => $targetFile['filename'],
            'size' => $targetFile['fileSize'],
            'length' => $targetFile['length'],
            'ext' => $targetFile['ext'],
            'source' => $targetFile['originPlatform'],
        ]);
    }

    /**
     * @return UploadFileService
     */
    private function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    /**
     * @return MaterialService
     */
    private function getCourseMaterialService()
    {
        return $this->service('Course:MaterialService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
