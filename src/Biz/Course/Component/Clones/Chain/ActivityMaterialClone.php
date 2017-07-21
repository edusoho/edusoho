<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Course\Dao\CourseMaterialDao;

class ActivityMaterialClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneActivityMaterials($source, $options);
    }

    private function cloneActivityMaterials($source, $options)
    {
        $newActivity = $options['newActivity'];
        $newCourseSet = $options['newCourseSet'];
        $newCourse = $options['newCourse'];
        $mediaSource = $source['mediaType'] === 'download' ? 'coursematerial' : 'courseactivity';
        $materials = $this->getMaterialDao()->findMaterialsByLessonIdAndSource($source['id'], $mediaSource);
        if (empty($materials)) {
            return;
        }

        $newMaterials = array();
        foreach ($materials as $material) {
            $newMaterial = $this->filterFields($material);

            $newMaterial['courseSetId'] = $newCourseSet['id'];
            $newMaterial['courseId'] = $newCourse['id'];
            $newMaterial['lessonId'] = $newActivity['id'];
            $newMaterial['source'] = $mediaSource;
            $newMaterial['userId'] = $this->biz['user']['id'];

            $newMaterials[] = $newMaterial;
        }

        $this->getMaterialDao()->batchCreate($newMaterials);

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
}
