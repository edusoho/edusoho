<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseSetDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseSetDaoImpl extends GeneralDaoImpl implements CourseSetDao
{
    protected $table = 'c2_course_set';

    public function get($id, $lock = false)
    {
        $courseSet = parent::get($id, $fields);
        if (!empty($courseSet)) {
            $courseSet = CourseSetSerialize::unserialize($courseSet);
        }
        return $courseSet;
    }

    public function update($id, array $fields)
    {
        $fields  = CourseSetSerialize::serialize($fields);
        $updated = parent::update($id, $fields);
        return CourseSetSerialize::unserialize($updated);
    }

    public function declares()
    {
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
