<?php

namespace Biz\Course\Copy\Impl;

use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Dao\CourseMaterialDao;

class ClassroomCourseCopy extends CourseCopy
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
        parent::__construct($biz);
    }

    /*
     * $source = $originalCourseSet
     * $config : courseId (course to copy), classroomId(?)
     */
    protected function _copy($source, $config = array())
    {
        $newCourseSet = $this->doCopyCourseSet($source);
        $this->doCopyMaterial($source, $newCourseSet);

        $course = $this->getCourseDao()->get($config['courseId']);

        $user        = $this->biz['user'];
        $courseSetId = $newCourseSet['id'];

        $newCourse                = $this->doCopy($course);
        $newCourse['isDefault']   = $course['isDefault'];
        $modeChange               = false;
        $newCourse['parentId']    = $course['id'];
        $newCourse['locked']      = 1; //默认锁定
        $newCourse['courseSetId'] = $courseSetId;
        $newCourse['creator']     = $user['id'];
        $newCourse['status']      = 'published';
        $newCourse['teacherIds']  = array($user['id']);

        $newCourse = $this->getCourseDao()->create($newCourse);
        $this->doCopyCourseMember($newCourse);

        $testpaperCopy = new CourseSetTestpaperCopy($this->biz);
        $testpaperCopy->copy($course, array('newCourseSet' => $newCourseSet, 'isCopy' => true));

        $this->childrenCopy($course, array(
            'newCourse'  => $newCourse,
            'modeChange' => $modeChange,
            'isCopy'     => true // 用于标记是复制还是clone，clone不需要记录parentId
        ));

        return $newCourse;
    }

    private function doCopyCourseSet($courseSet)
    {
        $fields = array(
            'type',
            'title',
            'subtitle',
            'tags',
            'categoryId',
            'serializeMode',
            'summary',
            'goals',
            'audiences',
            'cover',
            'categoryId',
            'recommended',
            'recommendedSeq',
            'recommendedTime',
            'discountId',
            'discount',
            'orgId',
            'orgCode'
        );
        $newCourseSet = array(
            'parentId' => $courseSet['id'],
            'status'   => 'published',
            'creator'  => $this->biz['user']['id'],
            'locked'   => 1 // 默认锁定
        );

        foreach ($fields as $field) {
            if (!empty($courseSet[$field]) || $courseSet[$field] == 0) {
                $newCourseSet[$field] = $courseSet[$field];
            }
        }

        return $this->getCourseSetDao()->create($newCourseSet);
    }

    private function doCopyMaterial($courseSet, $newCourseSet)
    {
        $materials = $this->getMaterialDao()->search(array('courseSetId' => $courseSet['id'], 'source' => 'coursematerial'), array(), 0, PHP_INT_MAX);
        if (empty($materials)) {
            return;
        }

        $fields = array(
            'title',
            'description',
            'link',
            'fileId',
            'fileUri',
            'fileMime',
            'fileSize',
            'type'
        );

        foreach ($materials as $material) {
            //仅处理挂在课程下的文件
            if ($material['courseId'] > 0) {
                continue;
            }

            $newMaterial = array(
                'courseSetId' => $newCourseSet['id'],
                'courseId'    => 0,
                'lessonId'    => 0,
                'source'      => 'coursematerial',
                'userId'      => $this->biz['user']['id'],
                'copyId'      => $material['id']
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
