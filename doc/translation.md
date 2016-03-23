#项目国际化翻译文档

目前edusoho已经支持语言国际化,实现的原理主要是 The Symfony Components Translation,主要翻译分为三部分:

1.页面(.HTML.twig, js)
2.后台(.php)
3.配置(data_dict.yml, menus_admin.yml)

页面具体实现方案如下.在以后的开发中如果涉及到一下模块,请按照本文档的指导编程, 以Translation支持你写的新模块.

1. data_dict 支持国际化：干掉html方式的定义改用宏的方式，语言串扫描工具，增加扫描data_dict.yml文件。
2. js: 生成一个js语言文件，
3. php中的
4. 菜单
5. 插件的语言包

实现方案
#### 1.dict_dict.yml
	该文件是数据字典的配置项
    
```yml
-'discountStatus:html':
-    unstart: <span class="text-muted">未开始</span>
-    running: <span class="text-success">进行中</span>
-    finished: <span class="text-muted">已结束</span>
-
 discountAuditStatus:
     passed: 已通过
     rejected: 未通过
...
```
上面代码中前面有`-`的已经被移除了,因为改类型的无法很好的支持trans,修改后的使用方法:
```twig
{#已经废弃#}
-{{ dict_text('courseStatus:html', course.status) }}

{#页面需要用到的,头部已用该文件,如果已存在字典项,直接使用下面的方法引用,没有按照该文件中的数据格式自己添加#}
{% import "TopxiaWebBundle:Common:data-dict-macro.html.twig" as dict_macro %}

{{ dict_macro.courseStatus( course.status) }}
```