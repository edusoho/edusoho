# CHANGELOG

## [Unreleased]

## [0.9.22] - 2018-05-02

* UUID 模式下， Dao 输出 经过 decode 之后的 `id`。

## [0.9.21] - 2018-04-21

* 修复 Dao Metadata 缓存目录创建的问题。

## [0.9.19] - 2018-04-26

* 修复 Dao Metadata 缓存文件的权限问题。

## [0.9.13] - 2018-03-02

* 修复 Dao 的 `update` 方法，当主键`id`为UUID时，无法更新的问题。

## [0.9.12] - 2018-02-24

* Dao 的 `batchCreate` 方法支持 UUID。

## [0.9.11] - 2018-02-22

* Dao支持UUID，需PHP 5.5+。@see https://github.com/ramsey/uuid/wiki/Ramsey%5CUuid-Codecs

## [0.9.8] - 2018-01-10

* 删除`Codeages\Biz\Framework\Context\CurrentUserInterface`接口，新增 `Codeages\Biz\Framework\Context\CurrentUser`类。
* 新增`Codeages\Biz\Framework\Context\BizAwareInterface`接口。
* 新增字符串工具类 `Codeages\Biz\Framework\Utility\Str`。
* 新增`BizCodeceptionModule`。
* 删除单元测试相关基类及其辅助类。
* 新增自动生成`env.php`的脚手架。
* 重命名：`Codeages\Biz\Framework\UnitTests\DatabaseSeeder` -> `Codeages\Biz\Framework\Testing\DatabaseSeeder`。

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