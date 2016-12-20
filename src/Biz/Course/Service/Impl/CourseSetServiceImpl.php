<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;
use Topxia\Service\Common\ServiceKernel;

class CourseSetServiceImpl extends BaseService implements CourseSetService
{
    public function tryManageCourseSet($id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException("Unauthorized");
        }

        $courseSet = $this->getCourseSetDao()->get($id);

        if (empty($courseSet)) {
            throw $this->createNotFoundException("CourseSet#{$id} Not Found");
        }

        if (!$this->hasCourseSetManagerRole($id)) {
            throw $this->createAccessDeniedException("Unauthorized");
        }

        return $courseSet;
    }

    public function getCourseSet($id)
    {
        return $this->getCourseSetDao()->get($id);
    }

    public function createCourseSet($courseSet)
    {
        if (!$this->hasCourseSetManagerRole()) {
            throw $this->createAccessDeniedException('You have no access to Course Set Management');
        }
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
            'learnMode'   => 'freeMode',
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

        $courseSet = $this->tryManageCourseSet($id);

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
        return $this->getCourseSetDao()->update($courseSet['id'], $fields);
    }

    public function updateCourseSetDetail($id, $fields)
    {
        $courseSet = $this->tryManageCourseSet($id);

        $fields = ArrayToolkit::parts($fields, array(
            'summary',
            'goals',
            'audiences'
        ));

        return $this->getCourseSetDao()->update($courseSet['id'], $fields);
    }

    public function changeCourseSetCover($id, $coverArray)
    {
        if (empty($coverArray)) {
            throw $this->createInvalidArgumentException("Invalid Param: cover");
        }
        $courseSet = $this->tryManageCourseSet($id);
        $covers    = array();
        foreach ($coverArray as $cover) {
            $file                   = $this->getFileService()->getFile($cover['id']);
            $covers[$cover['type']] = $file['uri'];
        }

        return $this->getCourseSetDao()->update($courseSet['id'], array('cover' => $covers));
    }

    public function deleteCourseSet($id)
    {
        //TODO
        //1. 判断该课程能否被删除
        //2. 删除时需级联删除课程下的教学计划、用户信息等等
        $courseSet = $this->tryManageCourseSet($id);
        return $this->getCourseSetDao()->delete($courseSet['id']);
    }

    protected function hasCourseSetManagerRole($courseSetId = 0)
    {
        $userId = $this->getCurrentUser()->getId();
        //TODO
        //1. courseSetId为空，判断是否有创建课程的权限
        //2. courseSetId不为空，判断是否有该课程的管理权限
        return true;
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
        return $this->biz->service('Course:CourseService');
    }

    protected function getTagService()
    {
        return $this->biz->service('Taxonomy:TagService');
    }

    protected function getFileService()
    {
        return ServiceKernel::instance()->createService('Content:FileService');
    }
}
