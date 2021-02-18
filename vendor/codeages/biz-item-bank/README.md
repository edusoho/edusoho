# 公共题库php-sdk

[开发文档](http://item-bank-docs.st.edusoho.cn/developer/)

## 开发
**安装依赖**
```
composer install --dev
```
**创建 var 目录：**

```
mkdir -p var/{cache,tmp,run,logs}; chmod 777 var/{cache,tmp,run,logs}
```

**创建数据库：**

```shell
CREATE DATABASE `biz_item_bank`;
```

**执行数据库变更脚本：**

```shell
bin/phpmig migrate
```