<?php

namespace Biz\Course\Copy\Impl;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseMaterialDao;

class ActivityMaterialCopy extends AbstractEntityCopy
{
    public function __construct($biz)
    {
        parent::__construct($biz, 'activity-material');
    }

    protected function copyEntity($source, $config = array())
    {
        $newActivity = $config['newActivity'];
        $isCopy = $config['isCopy'];
        $mediaSource = $source['mediaType'] === 'download' ? 'coursematerial' : 'courseactivity';
        $materials = $this->getMaterialDao()->findMaterialsByLessonIdAndSource($source['id'], $mediaSource);
        if (empty($materials)) {
            return;
        }

        $fields = $this->getFields();

        foreach ($materials as $material) {
            $newMaterial = array(
                'courseSetId' => $config['newCourseSetId'],
                'courseId' => $config['newCourseId'],
                'lessonId' => $newActivity['id'],
                'source' => $mediaSource,
                'userId' => $this->biz['user']['id'],
                'copyId' => $isCopy ? $material['id'] : 0,
            );
            foreach ($fields as $field) {
                if (!empty($material[$field])) {
                    $newMaterial[$field] = $material[$field];
                }
            }
            $this->getMaterialDao()->create($newMaterial);
        }

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
