# CHANGELOG

## HEAD (Unreleased)
_(none)_

* 新增：限制 Worker 在某个固定时间段才能执行 Job; 对应新增配置项，参见 [example/bootstrap.php](example/bootstrap.php)。
  * 新增：`hour_limits`
  * 新增：`workers[*]['consume_limiter']`

* 配置项变更，参见 [example/bootstrap.php](example/bootstrap.php)。
  * `rate_limiter` 改为 `rate_limits` ；
  * `workers[*]['consume_limiter']` 改为 `workers[*]['rate_limit']` ；

## 0.4.6 (2019-04-29)

* master 进程名 增加 worker 进程数量的显示，便于监控；
* 修复 undefined index rate_limiter 的错误。
