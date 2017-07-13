<?php

namespace Biz\Course\Copy\Chain;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseMaterialDao;

class ActivityMaterialCopy extends AbstractEntityCopy
{
    protected function copyEntity($source, $config = array())
    {
        $newActivity = $config['newActivity'];
        $isCopy = $config['isCopy'];
        $mediaSource = $source['mediaType'] === 'download' ? 'coursematerial' : 'courseactivity';
        $materials = $this->getMaterialDao()->findMaterialsByLessonIdAndSource($source['id'], $mediaSource);
        if (empty($materials)) {
            return;
        }

        $newMaterials = array();
        foreach ($materials as $material) {
            $newMaterial = $this->filterFields($material);

            $newMaterial['courseSetId'] = $config['newCourseSetId'];
            $newMaterial['courseId'] = $config['newCourseId'];
            $newMaterial['lessonId'] = $newActivity['id'];
            $newMaterial['source'] = $mediaSource;
            $newMaterial['userId'] = $this->biz['user']['id'];
            $newMaterial['copyId'] = $isCopy ? $material['id'] : 0;

            $newMaterials[] = $newMaterial;
        }

        $this->getCourseMaterialService()->batchCreateMaterials($newMaterials);

        return null;
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
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->biz->dao('Course:CourseMaterialDao');
    }

    protected function getCourseMaterialService()
    {
        return $this->biz->service('Course:MaterialService');
    }
}
