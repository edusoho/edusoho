<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\EduMaterialService;
use Topxia\Common\ArrayToolkit;

class EduMaterialServiceImpl extends BaseService implements EduMaterialService
{
    public function addEduMaterial($eduMaterial)
    {
        if (!ArrayToolkit::requireds($eduMaterial, array('gradeId', 'subjectId', 'materialId','materialName'))) {
            throw $this->createServiceException('缺少必要的字段，保存教材失败');
        }
        
        $material['gradeId']=$eduMaterial['gradeId'];
        $material['subjectId']=$eduMaterial['subjectId'];
        $material['materialId']=$eduMaterial['materialId'];
        $material['materialName']=$eduMaterial['materialName'];
        return $this->getEduMaterialDao()->addEduMaterial($eduMaterial);
    }

    public function findAllEduMaterials()
    {
        return $this->getEduMaterialDao()->findAllEduMaterials();
    }

    public function getEduMaterialByGradeIdAndSubjectId($gradeId,$subjectId)
    {
        return $this->getEduMaterialDao()->getEduMaterialByGradeIdAndSubjectId($gradeId,$subjectId);
    }

    private function getEduMaterialDao()
    {
        return $this->createDao('Course.EduMaterialDao');
    }
}