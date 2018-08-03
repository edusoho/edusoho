<?php

namespace Biz\System\Util;
use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class LogDataUtils
{
    public static function getTransConfig()
    {
        $config = array(
            'course' => array(
                'remove_student',
                'add_student',
                'create',
                'delete_thread',
                'update' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id'
                            )
                        )
                    ),
                    'getValue' => array(
                        '%title%' => 'showTitle',
                    )
                ),
                'publish',
                'close',
                'create_course'  => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id'
                            )
                        )
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                    )
                ),
                'update_course' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id'
                            )
                        )
                    ),
                    'getValue' => array(
                        '%title%' => 'showTitle',
                    )
                ),
            ),
            'classroom' => array(
                'create',
                'update' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id'
                            )
                        )
                    ),
                    'getValue' => array(
                        '%title%' => 'showTitle',
                    )
                ),
            ),
        );

        return $config;
    }

    public static function getTransPrefix($module, $action)
    {
        return array(
            'log.attr.' . $module . '.' . $action,
            'log.attr.' . $module,
            'log.attr',
        );
    }

    public static function shouldShowModal($module, $action)
    {
        $showModals = array(
            'course' => array(
                'create',
                'update',
                'create_course',
                'update_course',
            ),
            'classroom' => array(
                'create',
                'update',
            ),
        );
        if(array_key_exists($module, $showModals)){
            if(in_array($action, $showModals[$module])){
                return true;
            }
        }
        return false;
    }

    public static function trans($message, $module, $action)
    {
        $prefix = self::getTransPrefix($module, $action);
        foreach ($prefix as $v) {
            $transMessage = $v . '.' . $message;
            $trans = ServiceKernel::instance()->trans($transMessage, array(), null, null);
            if ($trans != $transMessage) {
                return $trans;
            }
        }
        return $message;
    }

    public static function serializeCourse($changeFields){
        $config = array(
            'buyExpiryTime' => array(
                'timeConvent'
            ),
            'expiryStartDate' => array(
                'timeConvent'
            ),
            'expiryEndDate' => array(
                'timeConvent'
            )
        );
        return self::serializeData($config, $changeFields);
    }

    public static function serializeCourseSet($changeFields){
        $config = array(
            'expiryValue' => array(
                'timeConvent'
            )
        );
        return self::serializeData($config, $changeFields);
    }

    private function serializeData($config, $changeFields){
        $data = array();
        $oldData = $changeFields['before'];
        $newData = $changeFields['after'];

        foreach ($oldData as $key => $oldValue){
            $newValue = $newData[$key];
            $old = $oldValue;
            $new = $newValue;

            if(isset($config[$key])){
                foreach ($config[$key] as $vv){
                    $old = self::$vv($oldValue);
                    $new = self::$vv($newValue);
                }
            }

            $data[$key] = array(
                'old' => $old,
                'new' => $new,
            );
        }


        return $data;
    }

    private function conventData($conventConfig, $changeFields){
        $data = array();

        foreach ($conventConfig as $key => $value){

            foreach ($value as $kk => $vv){
                $method = $vv['method'];
                $data[$kk] = self::$method($changeFields, $vv['params']);
            }

            $data[$key] = $value;
        }


        return $data;
    }


    private function timeConvent($time){
        if($time > 10000){
            $time = date("Y-m-d H:i:s", $time);
        }

        return $time;
    }



    private function thisValue($data, $params){
        return $params;
    }

    private function getValue($data, $params){
        if(isset($data[$params])){
            return $data[$params];
        }else{
            return '';
        }

    }




}
