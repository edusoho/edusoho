<?php

namespace Biz\Course\Copy;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseMaterialDao;

class MaterialCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
        return;
    }

    public function doCopy($source, $options)
    {
        $courseSet = $source;
        $newCourseSet = $options['newCourseSet'];

        $materials = $this->getMaterialDao()->search(
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

            $newMaterial = $this->partsFields($material);

            $newMaterial['courseSetId'] = $newCourseSet['id'];
            $newMaterial['courseId'] = 0;
            $newMaterial['lessonId'] = 0;
            $newMaterial['source'] = 'coursematerial';
            $newMaterial['userId'] = $this->biz['user']['id'];
            $newMaterials[] = $newMaterial;
        }
        $this->getMaterialDao()->batchCreate($newMaterials);
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
