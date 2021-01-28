<?php

namespace Biz\Activity\Copy;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseMaterialDao;

class ActivityMaterialCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
        // TODO: Implement preCopy() method.
    }

    public function doCopy($source, $options)
    {
        $newActivity = $options['newActivity'];
        $newCourseSet = $options['newCourseSet'];
        $newCourse = $options['newCourse'];
        $originActivity = $options['originActivity'];
        $mediaSource = $originActivity['mediaType'] === 'download' ? 'coursematerial' : 'courseactivity';
        $materials = $this->getMaterialDao()->findMaterialsByLessonIdAndSource($originActivity['id'], $mediaSource);

        if (empty($materials)) {
            return;
        }

        $newMaterials = array();
        foreach ($materials as $material) {
            $newMaterial = $this->partsFields($material);

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
