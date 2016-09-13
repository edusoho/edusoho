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

	private static function getModules(){
		return array(
			'user'=>'User.UserService',
			'course'=>'Course.CourseService',
			'classroom'=>'Classroom:Classroom.ClassroomService',
			'article'=>'Article.ArticleService'
		);
	}

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}