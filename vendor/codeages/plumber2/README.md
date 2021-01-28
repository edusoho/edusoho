# Plumber


## 安装

```
composer require codeages/plumber2
```

## 使用

```
Plumber2 v0.5.0

Usage:
  bin/plumber (run|start|restart|stop)  [--bootstrap=<file>]

Options:
  -h|--help    show this
  -b <file> --bootstrap=<file>  Load configuration file [default: plumber.php]
```

### 启动

```
bin/plumber start -b bootstrap-file-path   # `bootstrap-file-path`为启动配置文件路径
```

### 重启

```
bin/plumber restart -b bootstrap-file-path
```

### 停止

```
bin/plumber stop -b bootstrap-file-path
```

## 监控

**进程总数量监控：**

需添加进程数量的监控，避免进程异常退出；所有 Plumber 进程都会加上应用名的前缀，可以通过查询含应用名的进程数量。比如下述例子的 ExamplApp 。

```
$ ps aux | grep plumber
root     2061  0.0  0.5 316308 11992 ?        Ss   02:21   0:00 ExamplApp.plumber: master [workers: 2, bootstrap:/var/www/exampleapp/config/plumber.php]
root     2062  0.0  0.7 318492 15936 ?        S    02:21   0:00 ExamplApp.plumber: worker #0 listening test_beanstalk_topic topic [idle]
root     2063  0.0  0.7 318360 15764 ?        S    02:21   0:00 ExamplApp.plumber: worker #1 listening test_redis_topic topic [idle]
```

**Worker 进程的状态监控：**

需添加 Plumber Worker 进程的状态监控，进程状态有：

* `idle` : 空闲状态，表示当前Worker没有新任务。
* `busy` : 繁忙，表示当前Worker进程正在处理任务。
* `limited` : 消费任务被限制了，只有配置了任务消费限流策略才会出现此状态。
* `failed` : 进程出错，表示当前进程执行消费任务的代码时异常退出了。进程异常退出后，会尝试重启进程，连续 10 次重启失败后，进程就不会再重启，进程名会被标记为 `failed`。

需添加监控项：

* 各个状态的 Worker 进程数量。
* 当有 Worker 进程状态为 `failed` 时，需告警。

## Changelog

See [CHANGELOG.md](CHANGELOG.md).