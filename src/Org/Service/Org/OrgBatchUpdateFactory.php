<?php
namespace Org\Service\Org;

use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;


class OrgBatchUpdateFactory
{

    public static function getModuleService($module)
    {
        $modules = self::getModules();
        if (!array_key_exists($module, $modules)) {
            throw new InvalidArgumentException(array('模块%module%不存在,更新组织机构失败', array('%module%' => $module)));
        }
        return $modules[$module];
    }

    //batchUpdateOrg

    public static function getModules()
    {
        return array(
            'user'         => array(
                'protocol'=> 'biz',
                'service' => 'User:UserService'
            ),
            'course'       =>  array(
                'protocol'=> 'biz',
                'service' => 'Course:CourseService',
            ),
            'classroom'    => array(
                'protocol'=> 'biz',
                'service' => 'Classroom:ClassroomService'
            ),
            'article'      => 'Article.ArticleService',
            'announcement' => array(
                'protocol'=> 'biz',
                'service' => 'Announcement:AnnouncementService',
            )
        );
    }


    public static function getDispayModuleName($key)
    {
        $modules = array(
            'user'         => '用户',
            'course'       => '课程',
            'classroom'    => '班级',
            'article'      => '咨询',
            'announcement' => '网站公告'
        );
        if (array_key_exists($key, $modules)) {
            return $modules[$key];
        }
        throw new InvalidArgumentException('模块不存在,获取数据出错');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}