<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Course\Service\MaterialService;

class CourseSetMaterialClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->doCourseSetMaterialClone($source,$options);
    }

    private function doCourseSetMaterialClone($courseSet,$options)
    {
        $newCourseSet = $options['newCourseSet'];
        $this->doCloneMaterials($courseSet,$newCourseSet);
    }

    private function doCloneMaterials($courseSet,$newCourseSet)
    {
        $materials = $this->getCourseMaterialService()->searchMaterials(
            array('courseSetId' => $courseSet['id'], 'source' => 'coursematerial'),
            array(),
            0,
            PHP_INT_MAX
        );
        if (empty($materials)) {
            return;
        }

        $newMaterials = array();
        foreach ($materials as $material) {
            //仅处理挂在课程下的文件
            if ($material['courseId'] > 0) {
                continue;
            }

            $newMaterial = $this->filterFields($material);

            $newMaterial['courseSetId'] = $newCourseSet['id'];
            $newMaterial['courseId'] = 0;
            $newMaterial['lessonId'] = 0;
            $newMaterial['source'] = 'coursematerial';
            $newMaterial['userId'] = $this->biz['user']['id'];
            $newMaterials[] = $newMaterial;
        }

        $this->getCourseMaterialService()->batchCreateMaterials($newMaterials);
    }

    protected function getFields()
    {
        return array(
            'title',
            'description',
            'link',
            'fileId',
            'fileUri',
            'fileMime',
            'fileSize',
            'type',
        );
    }

    /**
     * @return MaterialService
     */
    protected function getCourseMaterialService()
    {
        return $this->biz->service('Course:MaterialService');
    }
}