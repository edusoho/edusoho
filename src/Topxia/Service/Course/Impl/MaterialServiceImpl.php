<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\MaterialService;

class MaterialServiceImpl extends BaseService implements MaterialService
{
    public function uploadMaterial($material)
    {
        $argument = $material;

        if (!ArrayToolkit::requireds($material, array('courseId', 'fileId', 'type'))) {
            throw $this->createServiceException('参数缺失，上传失败！');
        }

        $fields = array(
            'courseId'    => $material['courseId'],
            'lessonId'    => empty($material['lessonId']) ? 0 : $material['lessonId'],
            'description' => empty($material['description']) ? '' : $material['description'],
            'userId'      => $this->getCurrentUser()->id,
            'createdTime' => time(),
            'type'        => empty($material['type']) ? 'course' : $material['type']
        );

        if (empty($material['fileId'])) {
            if (empty($material['link'])) {
                throw $this->createServiceException('资料链接地址不能为空，添加资料失败！');
            }

            $fields['fileId'] = 0;
            $fields['link']   = $material['link'];
            $fields['title']  = empty($material['description']) ? $material['link'] : $material['description'];
        } else {
            $fields['fileId'] = (int) $material['fileId'];
            $file             = $this->getUploadFileService()->getThinFile($material['fileId']);

            if (empty($file)) {
                throw $this->createServiceException('文件不存在，上传资料失败！');
            }

            $fields['link']     = '';
            $fields['title']    = $file['filename'];
            $fields['fileSize'] = $file['fileSize'];
        }

        if (array_key_exists('copyId', $material)) {
            $fields['copyId'] = $material['copyId'];
        }

        $material = $this->getMaterialDao()->addMaterial($fields);

        $this->dispatchEvent("material.create", array('argument' => $argument, 'material' => $material));

        return $material;
    }

    public function deleteMaterial($courseId, $materialId)
    {
        $material = $this->getMaterialDao()->getMaterial($materialId);

        if (empty($material) || $material['courseId'] != $courseId) {
            throw $this->createNotFoundException('课程资料不存在，删除失败。');
        }

        $this->getMaterialDao()->deleteMaterial($materialId);

        $this->dispatchEvent("material.delete", $material);
    }

    public function deleteMaterialByMaterialId($materialId)
    {
        return $this->getMaterialDao()->deleteMaterial($materialId);
    }

    public function deleteMaterialsByLessonId($lessonId, $courseType = 'course')
    {
        $materials = $this->searchMaterials(
            array('lessonId' => $lessonId, 'type' => $courseType),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if ($materials) {
            foreach ($materials as $material) {
                $this->deleteMaterial($material['courseId'], $material['id']);
            }
        }

        return true;
    }

    public function deleteMaterialsByCourseId($courseId, $courseType = 'course')
    {
        $materials = $this->searchMaterials(
            array('courseId' => $courseId, 'type' => $courseType),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if ($materials) {
            foreach ($materials as $material) {
                $this->deleteMaterial($material['courseId'], $material['id']);
            }
        }

        return true;
    }

    public function getMaterial($courseId, $materialId)
    {
        $material = $this->getMaterialDao()->getMaterial($materialId);

        if (empty($material) || $material['courseId'] != $courseId) {
            return null;
        }

        return $material;
    }

    public function findLessonMaterials($lessonId, $start, $limit)
    {
        return $this->searchMaterials(
            array('lessonId' => $lessonId, 'type' => 'course'),
            array('createdTime', 'DESC'),
            $start, $limit
        );
    }

    public function searchMaterials($conditions, $orderBy, $start, $limit)
    {
        return $this->getMaterialDao()->searchMaterials($conditions, $orderBy, $start, $limit);
    }

    public function searchMaterialCount($conditions)
    {
        return $this->getMaterialDao()->searchMaterialCount($conditions);
    }

    protected function getMaterialDao()
    {
        return $this->createDao('Course.CourseMaterialDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }
}
