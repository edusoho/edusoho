# Biz RateLimiter

一个速率控制服务。

## 安装

```
composer require codeages/biz-rate-limiter
```

## 使用

在程序启动处加入：

```php
$biz->register(new RateLimiterServiceProvider());
```

创建limiter对象：

```php
$factory = $biz['ratelimiter.factory'];
$limiter = $factory('ip', 10, 600); // 速率：10/600秒

$remain = $limiter->check('127.0.0.1');
if ($remain === false) {    // 注意需使用恒等‘===’，因为$remain的值可能为0。
    echo '已达到限制';
} else {
    echo '还剩余'.$remain.'次';
}
```




