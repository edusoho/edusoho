<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;
use Topxia\Service\Common\ServiceKernel;

class CourseSetServiceImpl extends BaseService implements CourseSetService
{
    public function getCourseSet($id)
    {
        return $this->getCourseSetDao()->get($id);
    }

    public function createCourseSet($courseSet)
    {
        if (!ArrayToolkit::requireds($courseSet, array('title', 'type'))) {
            throw $this->createInvalidArgumentException($this->getKernel()->trans('缺少必要字段'));
        }
        if (!in_array($courseSet['type'], array('normal', 'live', 'liveOpen', 'open'))) {
            throw $this->createInvalidArgumentException($this->getKernel()->trans('无效的课程类型'));
        }

        $courseSet = ArrayToolkit::parts($courseSet, array(
            'type',
            'title'
        ));
        $created = $this->getCourseSetDao()->create($courseSet);

        // 同时创建默认的教学计划
        // XXX
        // 1. 是否创建默认教学计划应该是可配的；
        // 2. 教学计划的内容（主要是学习模式、有效期模式）也应该是可配的
        $defaultCourse = array(
            'courseSetId' => $created['id'],
            'title'       => '默认教学计划',
            'expiryMode'  => 'days',
            'expiryDays'  => 0,
            'learnMode'   => 'freeOrder',
            'isDefault'   => 1,
            'status'      => 'draft'
        );
        $this->getCourseDao()->create($defaultCourse);

        return $created;
    }

    public function updateCourseSet($id, $fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'categoryId', 'serializeMode'))) {
            throw $this->createInvalidArgumentException($this->getKernel()->trans('缺少必要字段'));
        }
        if (!in_array($fields['serializeMode'], array('none', 'serialized', 'finished'))) {
            throw $this->createInvalidArgumentException($this->getKernel()->trans('无效的连载类型'));
        }

        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'subtitle',
            'tags',
            'categoryId',
            'serializeMode',
            // 'summary',
            'smallPicture',
            'middlePicture',
            'largePicture'
        ));

        if (!empty($fields['tags'])) {
            $fields['tags'] = explode(',', $fields['tags']);
            $fields['tags'] = $this->getTagService()->findTagsByNames($fields['tags']);
            array_walk($fields['tags'], function (&$item, $key) {
                $item = (int) $item['id'];
            });
        }
        $fields  = CourseSetSerialize::serialize($fields);
        $updated = $this->getCourseSetDao()->update($id, $fields);
        return CourseSetSerialize::unserialize($updated);
    }

    public function deleteCourseSet($id)
    {
        //TODO
        //1. 判断该课程能否被删除
        //2. 删除时需级联删除课程下的教学计划、用户信息等等
        return $this->getCourseSetDao()->delete($id);
    }

    protected function validateCourseSet($courseSet)
    {
        if (!ArrayToolkit::requireds($courseSet, array('title', 'type'))) {
            throw $this->createInvalidArgumentException($this->getKernel()->trans('缺少必要字段'));
        }
        if (!in_array($courseSet['type'], array('normal', 'live', 'liveOpen', 'open'))) {
            throw $this->createInvalidArgumentException($this->getKernel()->trans('无效的课程类型'));
        }
    }

    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    protected function getTagService()
    {
        return $this->getKernel()->createService('Taxonomy.TagService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}

class CourseSetSerialize
{
    public static function serialize(array &$courseSet)
    {
        if (isset($courseSet['tags'])) {
            if (is_array($courseSet['tags']) && !empty($courseSet['tags'])) {
                $courseSet['tags'] = '|'.implode('|', $courseSet['tags']).'|';
            } else {
                $courseSet['tags'] = '';
            }
        }

        // if (isset($courseSet['teacherIds'])) {
        //     if (is_array($courseSet['teacherIds']) && !empty($courseSet['teacherIds'])) {
        //         $courseSet['teacherIds'] = '|'.implode('|', $courseSet['teacherIds']).'|';
        //     } else {
        //         $courseSet['teacherIds'] = null;
        //     }
        // }

        return $courseSet;
    }

    public static function unserialize(array $courseSet = null)
    {
        if (empty($courseSet)) {
            return $courseSet;
        }

        $courseSet['tags'] = empty($courseSet['tags']) ? array() : explode('|', trim($courseSet['tags'], '|'));

        // if (empty($courseSet['teacherIds'])) {
        //     $courseSet['teacherIds'] = array();
        // } else {
        //     $courseSet['teacherIds'] = explode('|', trim($courseSet['teacherIds'], '|'));
        // }

        return $courseSet;
    }

    public static function unserializes(array $courseSets)
    {
        return array_map(function ($courseSet) {
            return CourseSetSerialize::unserialize($courseSet);
        }, $courseSets);
    }
}
