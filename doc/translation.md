#项目国际化翻译文档

EduSoho已经支持语言国际化, 技术选型：
* [Symfony Translation Component](http://symfony.com/doc/current/components/translation/usage.html)
* [JsTranslationBundle](https://github.com/willdurand/BazingaJsTranslationBundle)

**!!重要的事情提前说：以后开发过程中要实时做国际化**
需要翻译的内容:
```
1. 配置文件(data_dict.yml, menus_admin.yml)
2. 前端(twig, js)
3. 后台(php)
4. 插件
```

### 一. data_dict.yml 数据字典的配置项

已经干掉了html方式的定义改用宏的方式
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


### 二. twig翻译修改

##### 1. 纯文本及变量占位符

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

##### 2. 文案中夹杂html标签和样式

各个语言文件中定义完整文案(含html)，如：
/src/Topxia/WebBundle/Resources/translations/messages.zh_CN.yml

```yml
尚未开通云视频提示: >
  <h4>
    您尚未开通云视频服务，无法使用资源管理功能，
    <a href="%settingCloudVideoUrl%">立即开通</a>
    或
    <a href="%showCloudVideoUrl%" target="_blank">了解云视频</a>
  </h4>
  <p>
  “云资源管理”提供完善的资源管理功能，您可以从课程、上传时间、资源类型等多个维度对资源进行管理，帮助您轻松掌控全站的视频、音频、图片、文档等资源，再也不用担心过期资源占用存储空间了。
  </p>
```

在twig中引用

```twig
{{ '尚未开通云视频提示'|trans({'%settingCloudVideoUrl%': path('admin_setting_cloud_video'), '%showCloudVideoUrl%': 'http://open.edusoho.com/show/cloud/video'})|raw }}
```


### 三. js翻译修改
    
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

### 四. php翻译修改
PHP中尽量不要做国际化，应该放到twig或js中做，实在不行controller里面做一下；
抛出的异常文案不要做国际化，参考[exception.md](exception.md)
格式为$this->getServiceKernel()->trans('翻译%foo%文本', array('%foo%' =>参数))


**!!重要的事情再说一遍：以后开发过程中要实!时!做国际化**
