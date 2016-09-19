<?php
namespace Org\Service\Org;

use Topxia\Service\Common\ServiceKernel;

class OrgBatchUpdateFactory {

	public static function getModuleService($module){
		 $modules = self::getModules();
		 if (!array_key_exists($module, $modules)) {
            throw new \InvalidArgumentException(self::getKernel()->trans('模块%module%不存在,更新组织机构失败',array('%module%' => $module)));
        }
        return $modules[$module];
    }


	public static function getModules(){
		return array(
			'user'=>'User.UserService',
			'course'=>'Course.CourseService',
			'classroom'=>'Classroom:Classroom.ClassroomService',
			'article'=>'Article.ArticleService'
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
        if (array_key_exists($key,$modules)) {
            return $modules[$key];
        }
        throw new \Exception("模块不存在,获取数据出错");
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}