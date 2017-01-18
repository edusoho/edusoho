<?php

namespace Biz\Course\Copy\Impl;

use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Dao\CourseMaterialDao;
use Biz\Course\Copy\AbstractEntityCopy;

class CourseSetCopy extends AbstractEntityCopy
{
    /**
     * 复制链说明：
     * CourseSet 课程信息
     * - Course 教学计划及相关信息
     * - Testpaper (课程下创建的Testpaper)
     * - Material （课程下上传的Material）
     *
     *
     * @param $biz
     */
    public function __construct($biz)
    {
        $this->biz        = $biz;
        $this->children   = array();
        $this->children[] = new CourseSetTestpaperCopy($biz);
        $this->children[] = new CourseCopy($biz);
    }

    /*
     * $source = $originalCourseSet
     * $config : courseId (course to copy), classroomId
     */
    protected function _copy($source, $config = array())
    {
        $newCourseSet = $this->doCopyCourseSet($source);
        $this->doCopyMaterial($source, $newCourseSet);

        $course = $this->getCourseDao()->get($config['courseId']);
        $this->childrenCopy($course, array('newCourseSet' => $newCourseSet));

        return $newCourseSet;
    }

    private function doCopyCourseSet($courseSet)
    {
        $newCourseSet = array(
            'type'          => $courseSet['type'],
            'title'         => $courseSet['title'],
            'subtitle'      => $courseSet['subtitle'],
            'tags'          => $courseSet['tags'],
            'categoryId'    => $courseSet['categoryId'],
            'serializeMode' => $courseSet['serializeMode'],
            'summary'       => $courseSet['summary'],
            'goals'         => $courseSet['goals'],
            'audiences'     => $courseSet['audiences'],
            'cover'         => $courseSet['cover'],
            'parentId'      => $courseSet['id'],
            'status'        => 'published',
            'creator'       => $this->biz['user']['id']
        );

        return $this->getCourseSetDao()->create($newCourseSet);
    }

    private function doCopyMaterial($courseSet, $newCourseSet)
    {
        $materials = $this->getMaterialDao()->search(array('courseSetId' => $courseSet['id'], 'source' => 'coursematerial'), array(), 0, PHP_INT_MAX);
        if (empty($materials)) {
            return;
        }

        foreach ($materials as $material) {
            //仅处理挂在课程下的文件
            if ($material['courseId'] > 0) {
                continue;
            }

            $newMaterial = array(
                'courseSetId' => $newCourseSet['id'],
                'courseId'    => 0,
                'lessonId'    => 0,
                'title'       => $material['title'],
                'description' => $material['description'],
                'link'        => $material['link'],
                'fileId'      => $material['fileId'],
                'fileUri'     => $material['fileUri'],
                'fileMime'    => $material['fileMime'],
                'fileSize'    => $material['fileSize'],
                'source'      => 'coursematerial',
                'userId'      => $this->biz['user']['id'],
                'type'        => $material['type'],
                'copyId'      => $material['id']
            );
            $this->getMaterialDao()->create($newMaterial);
        }
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->biz->dao('Course:CourseMaterialDao');
    }
}
