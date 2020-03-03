# 0.1.12 (2020-02-07)

* 添加 notification 组件

# 0.1.11 (2019-10-29)

* layout组件支持新开页面和active配置，支持new标签样式配置，侧边栏多层级时默认展开

# 0.1.10 (2019-10-22)

* 恢复 解决regeneratorRuntime is not defined引起的bug

# 0.1.9 (2019-10-10)

* 新增后台 layout 组件

* 修改直接引用dist/codeages-design.js 报错 regeneratorRuntime is not defined

# 0.1.8 (2019-07-09)

* upload组件  当选择文件为空时，使用上一次选择的文件而不是直接报错

upload.js

```
  catch(event) {
    //...
    if (!file) {
      return;
    }
    //...
  }

```

# 0.1.7 (2018-12-29)

* select组件  parent参数支持可配置

```
     this.options = {
      parent: props.parent || document,
    };
```

# 0.1.6 (2018-03-08)

* 添加色板内容

——————————————————————————————————

* add color chart


# 0.1.5 (2018-02-09)

* 第一个正式版本，包括btn等20多个组件

——————————————————————————————————

* The first official version, including more than 20 components, example btn etc.
