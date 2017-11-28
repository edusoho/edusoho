# CHANGELOG

## [Unreleased]

## [0.8.4] - 2017-09-14

* 增加 `setting:set` 命令。

## [0.8.3] - 2017-09-07

* 修复 `MigrationBootstrap` 在 PHP 5.3 下报错的问题。

## [0.8.2] - 2017-09-06

* 新增 `Codeages\Biz\Framework\Utility\Env`。
* 新增 `env:write` 命令。

## [0.8.1] - 2017-08-31

* RedisCache 新增 incr 方法。

## [0.8.0] - 2017-08-29

* 新增 Console 组件，`bin/biz`。
* 各个组件的 migration 不再自动注册，需调用命令 `bin/biz {...}:table` 系列命令，主动创建各个组件的 migration。

## [0.7.0] - 2017-08-29

* 新增 Queue 组件。

## [0.6.2] - 2017-08-14

* 表名 `target_log` 重命名为 `biz_targetlog`。
* `TargetlogServce` 的 `log` 方法 `context` 参数的键 `user_id`、`action`、`ip`，改为 `@user_id`、`@action`、`@ip`。

## [0.6.1] - 2017-08-09

* MigrationBootstrap 支持配置 table name。

## [0.6.0] - 2017-08-08

* 修复Dao缓存策略 RowStrategy 在查询不存在的记录时的缓存错误。
* 新增 SettingService。

## [0.5.7] - 2017-08-07

* 新增`Scheduler`组件的`job`retry机制。