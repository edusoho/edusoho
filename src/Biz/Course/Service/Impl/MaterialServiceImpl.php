<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Biz\Course\Dao\CourseMaterialDao;
use Biz\Course\MaterialException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Biz\File\UploadFileException;
use Codeages\Biz\Framework\Event\Event;

class MaterialServiceImpl extends BaseService implements MaterialService
{
    public function uploadMaterial($material)
    {
        $argument = $material;
        if (!ArrayToolkit::requireds($material, array('courseSetId', 'courseId', 'fileId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields = $this->_getMaterialFields($material);

        if (!empty($fields['fileId'])) {
            $courseMaterials = $this->searchMaterials(
                array(
                    'courseSetId' => $fields['courseSetId'],
                    'courseId' => $fields['courseId'],
                    'fileId' => $fields['fileId'],
                    'lessonId' => 0,
                    'type' => $fields['type'],
                ),
                array('createdTime' => 'DESC'), 0, PHP_INT_MAX
            );
            if ($courseMaterials) {
                $updateFields = array(
                    'lessonId' => $fields['lessonId'],
                    'source' => $fields['source'],
                    'description' => $fields['description'],
                );
                $material = $this->updateMaterial($courseMaterials[0]['id'], $updateFields, $argument);
            } else {
                $material = $this->addMaterial($fields, $argument);
            }
        } elseif (!empty($fields['link'])) {
            $material = $this->addMaterial($fields, $argument);
        }

        return $material;
    }

    public function addMaterial($fields, $argument)
    {
        $material = $this->getMaterialDao()->create($fields);

        $logType = 'openCourse' == $material['type'] ? 'open_course' : 'course';
        //$this->getLogService()->info($logType, 'add_material', "新增资料(#{$material['id']})", $material);
        $this->dispatchEvent('course.material.create', new Event($material, array('argument' => $argument)));

        return $material;
    }

    public function updateMaterial($id, $fields, $argument)
    {
        $sourceMaterial = $this->getMaterialDao()->get($id);
        $material = $this->getMaterialDao()->update($id, $fields);

        $this->dispatchEvent('course.material.update', new Event($material, array('argument' => $argument, 'sourceMaterial' => $sourceMaterial)));

        return $material;
    }

    public function deleteMaterial($courseSetId, $materialId)
    {
        $material = $this->getMaterialDao()->get($materialId);
        if (empty($material)) {
            $this->createNewException(MaterialException::NOTFOUND_MATERIAL());
        }

        $this->getMaterialDao()->delete($materialId);

        $logType = 'openCourse' == $material['type'] ? 'open_course' : 'course';
        $this->getLogService()->info($logType, 'delete_material', "移除资料(#{$material['id']})", $material);
        $this->dispatchEvent('course.material.delete', new Event($material));
    }

    public function findMaterialsByCopyIdAndLockedCourseIds($copyId, $courseIds)
    {
        return $this->getMaterialDao()->findByCopyIdAndLockedCourseIds($copyId, $courseIds);
    }

    public function findMaterialsByLessonIdAndSource($lessonId, $source)
    {
        return $this->getMaterialDao()->findMaterialsByLessonIdAndSource($lessonId, $source);
    }

    public function deleteMaterialByMaterialId($materialId)
    {
        return $this->getMaterialDao()->delete($materialId);
    }

    public function deleteMaterialsByLessonId($lessonId, $courseType = 'course')
    {
        $result = $this->getMaterialDao()->deleteByLessonId($lessonId, $courseType);

        $this->dispatchEvent('course.lesson.materials.delete', new Event(array('lessonId' => $lessonId)));

        return $result;
    }

    public function deleteMaterialsByCourseId($courseId, $courseType = 'course')
    {
        return $this->getMaterialDao()->deleteByCourseId($courseId, $courseType);
    }

    public function deleteMaterialsByCourseSetId($courseSetId, $courseType = 'course')
    {
        return $this->getMaterialDao()->deleteByCourseSetId($courseSetId, $courseType);
    }

    public function deleteMaterials($courseSetId, $fileIds, $courseType = 'course')
    {
        $conditions = array(
            'fileIds' => $fileIds,
            'type' => $courseType,
        );
        if ('openCourse' == $courseType) {
            $conditions['courseId'] = $courseSetId;
            $conditions['courseSetId'] = 0;
        } else {
            $conditions['courseSetId'] = $courseSetId;
        }

        $materials = $this->searchMaterials(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        if (!$materials) {
            return array();
        }

        foreach ($materials as $key => $material) {
            $this->deleteMaterial($courseSetId, $material['id']);
        }

        return $materials;
    }

    public function deleteMaterialsByFileId($fileId)
    {
        return $this->getMaterialDao()->deleteByFileId($fileId);
    }

    public function getMaterial($courseId, $materialId)
    {
        $material = $this->getMaterialDao()->get($materialId);
        if (empty($material) || $material['courseId'] != $courseId) {
            return null;
        }

        return $material;
    }

    public function findCourseMaterials($courseId, $start, $limit)
    {
        return $this->getMaterialDao()->search(array('courseId' => $courseId), array('createdTime' => 'ASC'), $start, $limit);
    }

    public function getMaterialCountByFileId($fileId)
    {
        return $this->getMaterialDao()->count(array('fileId' => $fileId));
    }

    public function searchMaterials($conditions, $orderBy, $start, $limit)
    {
        return $this->getMaterialDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function findMaterialsByIds($ids)
    {
        return $this->getMaterialDao()->findMaterialsByIds($ids);
    }

    public function countMaterials($conditions)
    {
        return $this->getMaterialDao()->count($conditions);
    }

    public function searchFileIds($conditions, $orderBy, $start, $limit)
    {
        $fileIdArray = $this->getMaterialDao()->searchDistinctFileIds($conditions, $orderBy, $start, $limit);
        if (empty($fileIdArray)) {
            return array();
        }

        return ArrayToolkit::column($fileIdArray, 'fileId');
    }

    public function searchMaterialCountGroupByFileId($conditions)
    {
        return $this->getMaterialDao()->countGroupByFileId($conditions);
    }

    public function findUsedCourseMaterials($fileIds, $courseId = 0)
    {
        $conditions = array(
            'fileIds' => $fileIds,
            'excludeLessonId' => 0,
        );
        if ($courseId) {
            $conditions['courseId'] = $courseId;
        }

        $materials = $this->searchMaterials(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $materials = ArrayToolkit::group($materials, 'fileId');
        $files = array();

        if ($materials) {
            foreach ($materials as $fileId => $material) {
                $files[$fileId] = ArrayToolkit::column($material, 'source');
            }
        }

        return $files;
    }

    public function findUsedCourseSetMaterials($fileIds, $courseSetId)
    {
        $conditions = array(
            'fileIds' => $fileIds,
            'excludeLessonId' => 0,
        );
        if ($courseSetId) {
            $conditions['courseSetId'] = $courseSetId;
        }

        $materials = $this->searchMaterials(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $materials = ArrayToolkit::group($materials, 'fileId');
        $files = array();

        if ($materials) {
            foreach ($materials as $fileId => $material) {
                $files[$fileId] = ArrayToolkit::column($material, 'source');
            }
        }

        return $files;
    }

    public function findFullFilesAndSort($materials)
    {
        if (!$materials) {
            return array();
        }

        $fileIds = ArrayToolkit::column($materials, 'fileId');
        $files = $this->getUploadFileService()->findFilesByIds($fileIds, $showCloud = 1);

        $files = ArrayToolkit::index($files, 'id');
        $sortFiles = array();
        foreach ($materials as $key => $material) {
            if (isset($files[$material['fileId']])) {
                $file = array_merge($material, $files[$material['fileId']]);
                $sortFiles[$key] = $file;
            }
        }

        return $sortFiles;
    }

    public function batchCreateMaterials($materials)
    {
        if (empty($materials)) {
            return array();
        }

        return $this->getMaterialDao()->batchCreate($materials);
    }

    private function _getMaterialFields($material)
    {
        $fields = array(
            'courseSetId' => $material['courseSetId'],
            'courseId' => $material['courseId'],
            'lessonId' => empty($material['lessonId']) ? 0 : $material['lessonId'],
            'description' => empty($material['description']) ? '' : $material['description'],
            'userId' => $this->getCurrentUser()->offsetGet('id'),
            'source' => isset($material['source']) ? $material['source'] : 'coursematerial',
            'type' => isset($material['type']) ? $material['type'] : 'course',
            'createdTime' => time(),
        );

        if (empty($material['fileId'])) {
            if (empty($material['link'])) {
                $this->createNewException(MaterialException::LINK_REQUIRED());
            }
            $fields['fileId'] = 0;
            $fields['link'] = $material['link'];
            $fields['title'] = empty($material['description']) ? $material['link'] : $material['description'];
        } else {
            $fields['fileId'] = (int) $material['fileId'];
            $file = $this->getUploadFileService()->getFile($material['fileId']);
            if (empty($file)) {
                $this->createNewException(UploadFileException::NOTFOUND_FILE());
            }
            $fields['link'] = '';
            $fields['title'] = $file['filename'];
            $fields['fileSize'] = $file['fileSize'];
        }

        if (array_key_exists('copyId', $material)) {
            $fields['copyId'] = $material['copyId'];
        }

        return $fields;
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->createDao('Course:CourseMaterialDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
