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
