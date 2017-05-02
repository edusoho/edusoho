<?php

namespace Biz\Course\Copy\Impl;

use Biz\Course\Dao\CourseMaterialDao;
use Biz\Course\Copy\AbstractEntityCopy;

class CourseMaterialCopy extends AbstractEntityCopy
{
    public function __construct($biz, $node)
    {
        parent::__construct($biz, $node);
    }

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
        $materials = $this->getMaterialDao()->search(
            array('courseSetId' => $courseSet['id'], 'source' => 'coursematerial'),
            array(),
            0,
            PHP_INT_MAX
        );
        if (empty($materials)) {
            return;
        }

        $fields = $this->getFields();

        foreach ($materials as $material) {
            //仅处理挂在课程下的文件
            if ($material['courseId'] > 0) {
                continue;
            }

            $newMaterial = array(
                'courseSetId' => $newCourseSet['id'],
                'courseId' => 0,
                'lessonId' => 0,
                'source' => 'coursematerial',
                'userId' => $this->biz['user']['id'],
                'copyId' => $material['id'],
            );

            foreach ($fields as $field) {
                if (!empty($material[$field])) {
                    $newMaterial[$field] = $material[$field];
                }
            }
            $this->getMaterialDao()->create($newMaterial);
        }
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->biz->dao('Course:CourseMaterialDao');
    }
}
