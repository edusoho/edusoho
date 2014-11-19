<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\EduMaterialService;
use Topxia\Common\ArrayToolkit;

class EduMaterialServiceImpl extends BaseService implements EduMaterialService
{
    public function getEduMaterial($id)
    {
        if (empty($id)) {
            return null;
        }
        return $this->getEduMaterialDao()->getEduMaterial($id);
    }

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

    public function deleteEduMaterial($id)
    {
        $eduMaterial=$this->getEduMaterial($id);
        if(empty($eduMaterial)){
            throw $this->createNotFoundException("需要删除对象不存在,删除失败");
        }
        return $this->getEduMaterialDao()->deleteEduMaterial($id);
    }

    public function findAllEduMaterials()
    {
        return $this->getEduMaterialDao()->findAllEduMaterials();
    }

    public function getEduMaterialByGradeIdAndSubjectId($gradeId,$subjectId)
    {
        return $this->getEduMaterialDao()->getEduMaterialByGradeIdAndSubjectId($gradeId,$subjectId);
    }

    public function updateEduMaterial($id,array $fields)
    {
        $eduMaterial = $this->getEduMaterial($id);
        if (empty($eduMaterial)) {
            throw $this->createNoteFoundException("教材数据(#{$id})不存在，更新教材失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('gradeId', 'subjectId', 'materialId'));
        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新教材失败！');
        }

        $this->filterEduMaterialFields($fields, $eduMaterial);

        return $this->getEduMaterialDao()->updateEduMaterial($id, $fields);
    }

    private function filterEduMaterialFields(&$eduMaterial, $releatedEduMaterial = null)
    {
        foreach (array_keys($eduMaterial) as $key) {
            switch ($key) {
                case 'gradeId':
                    $eduMaterial['gradeId'] = (int) $eduMaterial['name'];
                    if (empty($eduMaterial['gradeId'])) {
                        throw $this->createServiceException("年级不能为空，保存教材失败");
                    }
                    break;
                case 'subjectId':
                    $eduMaterial['subjectId'] = (int) $eduMaterial['subjectId'];
                    if (empty($eduMaterial['subjectId'])) {
                        throw $this->createServiceException("学科不能为空，保存教材失败");
                    }
                    break;
                case 'materialId':
                    $eduMaterial['materialId'] = (int) $eduMaterial['materialId'];
                    if (empty($eduMaterial['materialId'])) {
                        throw $this->createServiceException("教材分类不能为空，保存教材失败");
                    }
                    $material=$this->getCategoryService()->getCategory($eduMaterial['materialId']);
                    if(empty($material)){
                        throw $this->createServiceException("教材分类(#{$eduMaterial['materialId']})不存在，保存教材失败");
                    }
                    $eduMaterial['materialName']=$material['name'];
                    break;
            }
        }

        return $eduMaterial;
    }

    private function getCategoryService()
    {
        return $this->createService('Taxonomy.CategoryService');
    }

    private function getEduMaterialDao()
    {
        return $this->createDao('Course.EduMaterialDao');
    }
}