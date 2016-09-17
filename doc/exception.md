#EduSoho异常规范

##### 1.所有的异常都用自定义的类型，不要用php自带的，抛异常直接用throw new XxxException()，不要用createServiceException()之类的方法；

Bad

```php
throw $this->createServiceException('字段名称不能为空！');
```

Good

```php
use Topxia\Common\Exception\InvalidArgumentException;
```

```php
throw new InvalidArgumentException('用户昵称格式不正确,设置帐号失败');
```

##### 2.所有的非异常文案提示里面如果有变量要插入，都要用sprintf或strtr，不要用"用户#{$id}不存在"之类的形式；

Bad

```php
$this->getLogService()->info('user', 'lock', "封禁用户{$user['nickname']}(#{$user['id']})");
```

Good

```php
$this->getLogService()->info('user', 'lock', sprintf('封禁用户%s(#%u)', $user['nickname'], $user['id']));
```

```php
$this->getLogService()->info('user', 'lock', strtr('封禁用户%nickname%(#%id%)', array(
    '%nickname%' => $user['nickname'], '%id%' => $user['id'])));
```

##### 3.所有异常提示文案提示里面如果有变量要插入，都要用以下方式：

Bad（无法做国际化）

```php
new ParseException(sprintf('获取(%s)页面内容失败！', $rurl ));
```

Good（传入一个array，用法跟strtr一致）

```php
new ParseException(array('获取(%url%)页面内容失败！', array('%url%' =>$rurl )));
```

##### 4.所有异常提示文案的最后不要写标点；

Bad

```
('优酷视频地址不正确！')
('解析优酷视频页面信息失败!')
('解析优酷视频页面信息失败!!')
('解析QQ视频ID失败!....')
```

Good

```
('优酷视频地址不正确')
('解析优酷视频页面信息失败')
('解析优酷视频页面信息失败')
('解析QQ视频ID失败')
```