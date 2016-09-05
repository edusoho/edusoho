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
#### 1.1.data_dict.yml
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

{#/var/www/edusoho/src/Topxia/AdminBundle/Resources/views/Course/course-chooser.html.twig   demo地址#}
{#页面需要用到的,头部已用该文件,如果已存在字典项,直接使用下面的方法引用,没有按照该文件中的数据格式自己添加#}
{% import "TopxiaWebBundle:Common:data-dict-macro.html.twig" as dict_macro %}

{{ dict_macro.courseStatus( course.status) }}
```


#### 2.1.twig
    twig页面翻译修改
    
```twig
{#/var/www/edusoho/src/Topxia/WebBundle/Resources/views/Coin/bill-bar.html.twig#}

<a>我的现金账单</a>
<a>我的邀请码</a>

<a>{{'我的现金账单'|trans}}</a>
<a>{{'我的邀请码'|trans}}</a>
```
上述代码为twig页面普遍翻译文本，格式为{{'需要翻译的文本'|trans}}，其中不要添加空格以及翻译文本内容使用单引号包含，这样避免文本自动查找遗漏。以下位加有参数的翻译文本：

```twig
{#/var/www/edusoho/src/Topxia/WebBundle/Resources/views/Coin/bill-bar.html.twig#}

<a>我的{{setting('coin.coin_name')|default('虚拟币')}}</a>

<a>{{'我的%coin_name%'|trans({'%coin_name%': setting('coin.coin_name')|default('虚拟币'|trans)})}}</a>
```
有参数的翻译文本格式为：{{'翻译文%foo%本'|trans({'%foo%':参数})}}
注意：参数后|default('虚拟币'|trans)(见上)中如有需翻译文本，目前不匹配自动查找的正则，无法自动添加词条到messages.*.yml中

#### 2.2.js
	js页面翻译修改
    
```js
{#/var/www/edusoho/src/Topxia/WebBundle/Resources/public/js/controller/testpaper/testpaper-form.js#}

Notify.danger('试卷题目总数量不能为0。');

Notify.danger(Translator.trans('试卷题目总数量不能为0。'));
```
格式为Translator.trans('翻译文本'),
以下未有参数的（以上面代码为例）：
```js
Notify.danger(Translator.trans('试卷题目总数量不%foo%能为0。',{foo:123}));

```
格式为：Translator.trans('翻译%foo%文本',{foo:参数})
同样以上不要随意在里面添加空格，统一使用单引号

#### 3
	php页面翻译修改
    
```php
$this->setFlashMessage('success', '基础信息保存成功。');
$this->setFlashMessage('danger', '不能修改已绑定的手机。');

$this->setFlashMessage('success', $this->getServiceKernel()->trans('基础信息保存成功。'));
$this->setFlashMessage('danger', $this->getServiceKernel()->trans('不能修改已绑定的手机。'));

```
格式为：$this->getServiceKernel()->trans('翻译文本')
以下为带参数的：
```php
{#/var/www/edusoho/src/Topxia/WebBundle/Command/ThemeRegisterCommand.php#}
throw new \RuntimeException('主题目录{$themeDir}不存在！');

throw new \RuntimeException($this->getServiceKernel()->trans('主题目录%themeDir%不存在！', array('%themeDir%' =>$themeDir )));

```
格式为$this->getServiceKernel()->trans('翻译%foo%文本', array('%foo%' =>参数 ))，
同样以上不要随意在里面添加空格，统一使用单引号