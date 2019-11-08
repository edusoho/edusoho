##
`1. 原有的permissions.yml文件格式修改成支持一对多的形式`

修改前：
 ```
#教学
admin_course: admin_v2_teach  #教学
#课程管理权限
admin_course_show: admin_v2_course_show #课程管理
admin_course_manage: admin_v2_course_manage  #课程管理
...........
````
修改后：
```
#教学
admin_course: ［admin_v2_teach]  #教学
#课程管理权限
admin_course_show: ［admin_v2_course_show] #课程管理
admin_course_manage: ［admin_v2_course_manage，admin_v2_course_manage_2]  #课程管理
```
##

`2. 新写一个permissions转换的处理类，专门反向转换perlissions.yml`

`这样后面方便维护，只需要维护一个配置文件`

处理后的期望效果：
```
array(

    'admin_v2_teach' => 'admin_course',

    'admin_v2_course_show' => 'admin_course_show',

    'admin_v2_course_manage' => 'admin_v2_course_manage',

    'admin_v2_course_manage_2' => 'admin_course_manage',

)
```
##
`3. 新老版权限保存处理`

rolesTreeTrans($tree)  方法种拼接数据的时候带上对应的另一个版本的权限

如： 

```
private function rolesTreeTrans($tree)
{
    foreach ($tree as &$child) {
        $child['name'] = $this->trans($child['name'], array(), 'menu');
        $child['permissions'] = array('admin_v2_setting');//对应拼接上另一个版本的权限
        if (isset($child['children'])) {
            $child['children'] = $this->rolesTreeTrans($child['children']);
        }
    }

    return $tree;
}
```

##
`4. js提交的时候拼接选中的节点的permissions`

##
`5 permissions 注意的点` 

1. 老版对应新版时左边栏对应的权限必须一对多附带上 新版group和顶部导航权限
2. 新版存在迁移的页面 左边栏对应的权限必须一对多附带上 旧版顶部导航权限