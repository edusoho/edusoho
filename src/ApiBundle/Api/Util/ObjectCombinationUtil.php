<?php

namespace ApiBundle\Api\Util;

use AppBundle\Common\ArrayToolkit;

class ObjectCombinationUtil
{
    private $biz;

    private $serviceMap = array(
        'user' => 'User:UserService',
        'course' => 'Course:CourseService',
        'courseSet' => 'Course:CourseSetService'
    );

    private $methodMap = array(
        'user' => 'findUsersByIds',
        'course' => 'findCoursesByIds',
        'courseSet' => 'findCourseSetsByIds'
    );

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $targetObjectType
     * @param $sourceObj
     * @param array $targetIdFields
     */
    public function single(&$sourceObj, array $targetIdFields, $targetObjectType = 'user')
    {
        if (!$sourceObj) {
            return;
        }

        $targetIds = $this->findTargetIds($sourceObj, $targetIdFields);

        $targetObjects = $this->findTargetObjects($targetObjectType, $targetIds);
        $this->replaceSourceObject($targetObjects, $sourceObj, $targetIdFields);
    }

    public function multiple(&$sourceObjects, array $targetIdFields, $targetObjectType = 'user')
    {

        if (!$sourceObjects) {
            return;
        }
        
        $targetIds = array();
        foreach ($sourceObjects as $sourceObject) {
            $tempTargetIds = $this->findTargetIds($sourceObject, $targetIdFields);
            $this->pushIdToArray($targetIds, $tempTargetIds);
        }

        $targetObjects = $this->findTargetObjects($targetObjectType, $targetIds);
        foreach ($sourceObjects as &$sourceObject) {
            $this->replaceSourceObject($targetObjects, $sourceObject, $targetIdFields);
        }
    }

    private function findTargetIds($sourceObj, $targetIdFields)
    {
        $targetIds = array();
        foreach ($targetIdFields as $targetIdField) {
            $targetIdValue = $sourceObj[$targetIdField];
            $this->pushIdToArray($targetIds, $targetIdValue);
        }

        return $targetIds;
    }

    /**
     * @param $userIds
     * @return mixed
     */
    private function findTargetObjects($targetObjectType, $targetIds)
    {
        $targetIds = array_values(array_unique($targetIds));
        $method = $this->methodMap[$targetObjectType];
        $targetObjects = $this->biz->service($this->serviceMap[$targetObjectType])->{$method}($targetIds);
        return ArrayToolkit::index($targetObjects, 'id');
    }

    private function replaceSourceObject($targetObjects, &$sourceObj, $targetIdFields)
    {
        foreach ($targetIdFields as $targetIdField) {
            $newField = str_replace('Id', '', $targetIdField);
            $targetIdValue = $sourceObj[$targetIdField];
            $sourceObj[$newField] = array();

            if (is_array($targetIdValue)) {
                foreach ($targetIdValue as $targetId) {
                    if (isset($targetObjects[$targetId])) {
                        array_push($sourceObj[$newField], $targetObjects[$targetId]);
                    }
                }
            } else {
                if (isset($targetObjects[$targetIdValue])) {
                    $sourceObj[$newField] = $targetObjects[$targetIdValue];
                } else {
                    $sourceObj[$newField] = null;
                }

            }

            if ($targetIdField !== $newField) {
                unset($sourceObj[$targetIdField]);
            }
        }
    }


    private function pushIdToArray(&$sourceArr, $idValue)
    {
        if (is_array($idValue)) {
            foreach ($idValue as $idV) {
                array_push($sourceArr, $idV);
            }
        } else {
            array_push($sourceArr, $idValue);
        }
    }
}