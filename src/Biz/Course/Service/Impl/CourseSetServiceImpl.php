<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;

class CourseSetServiceImpl extends BaseService implements CourseSetService
{
    public function getCourseSet($id)
    {
        return $this->getCourseSetDao()->get($id);
    }

    public function createCourseSet($courseSet)
    {
        if (!ArrayToolkit::requireds($courseSet, array('title', 'type'))) {
            throw $this->createInvalidArgumentException("Lack of required fields");
        }
        if (!in_array($courseSet['type'], array('normal', 'live', 'liveOpen', 'open'))) {
            throw $this->createInvalidArgumentException("Invalid Param: type");
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
        $this->getCourseService()->createCourse($defaultCourse);

        return $created;
    }

    public function updateCourseSet($id, $fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'categoryId', 'serializeMode'))) {
            throw $this->createInvalidArgumentException("Lack of required fields");
        }
        if (!in_array($fields['serializeMode'], array('none', 'serialized', 'finished'))) {
            throw $this->createInvalidArgumentException("Invalid Param: serializeMode");
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
        return $this->getCourseSetDao()->update($id, $fields);
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
            throw $this->createInvalidArgumentException("Lack of Required Fields");
        }
        if (!in_array($courseSet['type'], array('normal', 'live', 'liveOpen', 'open'))) {
            throw $this->createInvalidArgumentException("Invalid Param: type");
        }
    }

    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }
}
