<?php

namespace Biz\Course\Copy\Chain;

use Biz\Course\Copy\AbstractEntityCopy;

class CourseMaterialCopy extends AbstractEntityCopy
{
    /**
     * @param $source array oldCourse
     * @param array $config $config['newCourse'] = newCourse
     */
    protected function copyEntity($source, $config = array())
    {
        $newCourseSet = $config['newCourseSet'];

        return $this->doCopyMaterial($source, $newCourseSet);
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

    private function doCopyMaterial($courseSet, $newCourseSet)
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

            $newMaterial['copyId'] = $material['id'];

            $newMaterials[] = $newMaterial;
        }

        $this->getCourseMaterialService()->batchCreateMaterials($newMaterials);
    }

    protected function getCourseMaterialService()
    {
        return $this->biz->service('Course:MaterialService');
    }
}
