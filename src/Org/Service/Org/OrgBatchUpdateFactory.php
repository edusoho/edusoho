<?php 
namespace Org\Service\Org;

class OrgBatchUpdateFactory {

	public static function getModuleService($module){
		 $modules = self::getModules();
		 if (!array_key_exists($module, $modules)) {
            throw new \InvalidArgumentException("模块{$module}不存在,更新组织机构失败");
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
}