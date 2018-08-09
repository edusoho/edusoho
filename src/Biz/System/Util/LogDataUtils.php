<?php

namespace Biz\System\Util;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\ArrayToolkit;

class LogDataUtils
{
    public static function getTransConfig()
    {
        $config = array(
            'course' => array(
                'create' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                    ),
                ),
                'update' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'showTitle',
                    ),
                ),
                'create_course' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                    ),
                ),
                'update_course' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'showTitle',
                    ),
                ),
                'add_student' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                        '%nickname%' => 'nickname',
                        '%remark%' => 'remark',
                    ),
                ),
                'remove_student' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                        '%nickname%' => 'nickname',
                    ),
                ),
                'delete_thread' => array(
                    'getValue' => array(
                        '%title%' => 'title',
                    ),
                ),
                'publish' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                    ),
                ),
                'close' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                    ),
                ),
                'create_lesson' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                    ),
                ),
                'add_task' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                    ),
                ),
            ),
            'classroom' => array(
                'create' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                    ),
                ),
                'update' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'showTitle',
                    ),
                ),
            ),
        );

        return $config;
    }

    public static function shouldShowModal($module, $action)
    {
        $showModals = array(
            'course' => array(
                'update',
                'update_course',
            ),
            'classroom' => array(
                'update',
            ),
        );
        if (array_key_exists($module, $showModals)) {
            if (in_array($action, $showModals[$module])) {
                return true;
            }
        }

        return false;
    }

    public static function trans($message, $module, $action)
    {
        $prefixs = self::getTransPrefix($module, $action);
        foreach ($prefixs as $prefix) {
            $transMessage = $prefix.'.'.$message;
            $trans = ServiceKernel::instance()->trans($transMessage, array(), null, null);
            if ($trans != $transMessage) {
                return $trans;
            }
        }

        return $message;
    }

    public static function getChangeFields()
    {
    }

    public static function serializeCourse($oldCourse, $fields)
    {
        $changeFields = ArrayToolkit::changes($oldCourse, $fields);
        $config = array(
            'buyExpiryTime' => array(
                'timeConvent',
            ),
            'expiryStartDate' => array(
                'timeConvent',
            ),
            'expiryEndDate' => array(
                'timeConvent',
            ),
        );

        $changeFields = self::serializeData($config, $changeFields);

        $changeFields['id'] = $oldCourse['id'];
        $changeFields['showTitle'] = $oldCourse['title'];

        return $changeFields;
    }

    public static function serializeCourseSet($courseSet, $fields)
    {
        $changeFields = ArrayToolkit::changes($courseSet, $fields);
        $config = array(
            'expiryValue' => array(
                'timeConvent',
            ),
        );

        $changeFields = self::serializeData($config, $changeFields);
        $changeFields['id'] = $courseSet['id'];
        $changeFields['showTitle'] = $courseSet['title'];

        return $changeFields;
    }

    public static function serializeClassroom($classroom, $fields)
    {
        $changeFields = ArrayToolkit::changes($classroom, $fields);
        $config = array(
            'expiryValue' => array(
                'timeConvent',
            ),
        );

        $changeFields = self::serializeData($config, $changeFields);
        $changeFields['id'] = $classroom['id'];
        $changeFields['showTitle'] = $classroom['title'];

        return $changeFields;
    }

    private function serializeData($config, $changeFields)
    {
        $data = array();
        $oldData = $changeFields['before'];
        $newData = $changeFields['after'];

        foreach ($oldData as $key => $oldValue) {
            $newValue = $newData[$key];
            $old = $oldValue;
            $new = $newValue;

            if (isset($config[$key])) {
                foreach ($config[$key] as $function) {
                    $old = self::$function($oldValue);
                    $new = self::$function($newValue);
                }
            }

            $data[$key] = array(
                'old' => $old,
                'new' => $new,
            );
        }

        return $data;
    }

    public static function getTransPrefix($module, $action)
    {
        return array(
            'log.attr.'.$module.'.'.$action,
            'log.attr.'.$module,
            'log.attr',
        );
    }

    private function timeConvent($time)
    {
        if ($time > 10000) {
            $time = date('Y-m-d H:i:s', $time);
        }

        return $time;
    }
}
