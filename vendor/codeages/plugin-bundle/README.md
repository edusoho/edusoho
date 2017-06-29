[![Build Status](https://travis-ci.org/codeages/plugin-bundle.svg?branch=master)](https://github.com/codeages/plugin-bundle)

# README

## 编写一个插件

### 目录结构

如果您的插件名称为`Demo`那么目录结构为：

```
plugins/
  DemoPlugin/
    Biz/
      Dao/
      Service/
    Controller/
    Migrations/
    Resources/
    Scripts/
      database.sql
      InstallScript.php
    DemoPlugin.php
    plugin.json
```

### 插件的源信息

即插件目录下的`plugin.json`：

```
{
    "code": "Demo",
    "name": "演示插件",
    "description": "这是一个演示插件",
    "author": "EduSoho官方",
    "version": "1.0.0",
    "support_version": "7.2.0"
}
```

### 插件的引导文件

即插件目录下的`DemoPlugin.php`：

```
<?php
namespace DemoPlugin;

use Codeages\PluginBundle\System\PluginBase;

class DemoPlugin extends PluginBase
{

}
```

`DemoPlugin`类必须继承自`Codeages\PluginBundle\System\PluginBase`类。

### 插件的注册/注销

** 注册 **

```
app/console plugin:register Demo
```

** 注销 **

```
app/console plugin:remove Demo
```