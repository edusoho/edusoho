# EduSoho插件应用开发

## 第０节　准备

请使用Linux / Mac OS做为EduSoho开发环境。开发EduSoho插件之前，请先到 [EduSoho开发云平台](http://open.edusoho.com/)申请成为开发者，并创建应用。
本文档，假设你创建的插件应用名称为：Example。

### 创建插件代码结构

进入到EduSoho目录，执行：

    app/console plugin:create Example

此命令在EduSoho的plugin目录下创建，Example插件的基础目录结构及文件。

### 注册插件

进入到EduSoho目录，执行：

    app/console plugin:register Example

此命令是把Example注册到EduSoho系统，这样EduSoho才会载入Example插件。

## 第１节　开发Hello World

Coming soon...

## 第２节　目录结构介绍

Coming soon...

## 第３节 ...

## 第４节 ...

## 第５节 ...

## 第６节 ...


## 学习资料

EduSoho基于一系列的开源框架/类库搭建，在做插件开发之前，还需学习以下开源框架及类库的使用。

  * Symfony2: http://symfony.com/
  * Twig: http://twig.sensiolabs.org/documentation
  * Seajs: http://seajs.org/docs/
  * Arale: http://aralejs.org/
  * Bootstrap: http://getbootstrap.com/
  * jQuery: http://jquery.com/

## FAQ

### 页面中如何引用Javascript ?

  1. 创建你的javascript文件 plugins/Example/ExampleBundle/public/js/your_script.js
  2. 在twig代码中，加入以下代码：
      ```
      {% set script_controller = 'examplebundle/your_script' %}

      {% do load_script('examplebundle/your_script') %}

      <script>app.load('examplebundle/your_script')</script>
      ```