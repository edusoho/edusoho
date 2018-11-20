<?php

namespace ApiBundle\Api\Util;

use AppBundle\Common\ArrayToolkit;

class ObjectCombinationUtil
{
    private $biz;

    private $serviceMap = array(
        'user' => 'User:UserService',
        'course' => 'Course:CourseService',
        'courseSet' => 'Course:CourseSetService',
        'classroom' => 'Classroom:ClassroomService',
    );

    private $methodMap = array(
        'user' => 'findUsersByIds',
        'course' => 'findCoursesByIds',
        'courseSet' => 'findCourseSetsByIds',
        'classroom' => 'findClassroomsByIds',
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

    /**
     * 将 指定属性替换为对象
     * 如 multiple($orderLogs, array('user_id'), 'user')，会将 orderLogs 中的 user_id 替换为 user对象
     *
     * @param $targetObjectType 分为 user, course, courseset, 见全局变量 $serviceMap
     */
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

    /**
     * 指定数组中的值替换为相应对象中的指定属性
     * 如
     *  $orderItems = array(
     *      array(
     *          'user_id' => 1,
     *      ),
     *      array(
     *          'user_id' => 2,
     *      ),
     *  );
     *
     *  replaceWithObjValue(
     *      $orderItems,
     *      array(
     *          'user_id' => array(
     *              'nickname' => 'nickname',
     *              'mobile' => 'verifiedMobile'
     *          )
     *      ),
     *      'user'
     *  )
     *
     * 结果为
     *  $orderItems = array(
     *      array(
     *          'nickname' => {id=1的user的nickname属性},
     *          'mobile' => {id=1的user的verifiedMobile属性},
     *      ),
     *      array(
     *          'nickname' => {id=2的user的nickname属性},
     *          'mobile' => {id=2的user的verifiedMobile属性},
     *      )
     *  )
     */
    public function replaceWithObjValue(&$sourceObjects, array $targetIdFields, $targetObjectType = 'user')
    {
        $keys = array_keys($targetIdFields);
        $this->multiple($sourceObjects, $keys, $targetObjectType);

        foreach ($sourceObjects as &$sourceObj) {
            foreach ($targetIdFields as $key => $attrs) {
                $data = $sourceObj[$key];
                unset($sourceObj[$key]);
                foreach ($attrs as $attrKey => $attrValue) {
                    $sourceObj[$attrKey] = $data[$attrValue];
                }
            }
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
     *
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
