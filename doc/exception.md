#EduSoho异常规范

##### 1. 所有的异常都用自定义的类型，不要用php自带的，抛异常直接用throw new XxxException()，不要用createServiceException()之类的方法；

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

##### 2. 所有的非异常文案提示里面如果有变量要插入，都要用sprintf或strtr，不要用"用户#{$id}不存在"之类的形式；

Bad

```php
$this->getLogService()->info('user', 'lock', "封禁用户{$user['nickname']}(#{$user['id']})");
```

Good（日志以后改成英文）

```php
$this->getLogService()->info('user', 'lock', sprintf('封禁用户%s(#%u)', $user['nickname'], $user['id']));
```

```php
$this->getLogService()->info('user', 'lock', strtr('封禁用户%nickname%(#%id%)', array(
    '%nickname%' => $user['nickname'], '%id%' => $user['id'])));
```

##### 3. 所有含中文异常提示文案提示里面如果有变量要插入，都要用以下方式：

Bad（无法做国际化，如果提示全部英文则可以用sprintf）

```php
new ParseException(sprintf('获取(%s)页面内容失败', $rurl ));
```

Good（传入一个array，用法跟strtr一致）

```php
new ParseException(array('获取(%url%)页面内容失败', array('%url%' =>$rurl )));
```

```php
new ParseException(sprintf('Fail to get response from %s', $rurl ));
```

##### 4. 所有异常提示文案的最后不要写标点；

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

##### 5. 选择合适的异常类；

可参考src/Topxia/Service/User/Impl/UserServiceImpl.php
!!异常类的种类还没完善，究竟怎么样算合适也没有明确边界，目前只能凭经验，还需讨论

注意：部分异常类有自定义参数，具体请看源码/src/Topxia/Common/Exception

```php
public function changeUserRoles($id, array $roles)
{
    if (empty($roles)) {
        throw new InvalidArgumentException('用户角色不能为空');
    }

    $user = $this->getUser($id);

    if (empty($user)) {
        throw new ResourceNotFoundException('User', $id, '设置用户角色失败');
    }

    if (!in_array('ROLE_USER', $roles)) {
        throw new UnexpectedValueException('用户角色必须包含ROLE_USER');
    }

    $allowedRoles = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');

    $notAllowedRoles = array_diff($roles, $allowedRoles);

    if (!empty($notAllowedRoles)) {
        throw new UnexpectedValueException('用户角色不正确，设置用户角色失败。');
    }

    $this->getUserDao()->updateUser($id, UserSerialize::serialize(array('roles' => $roles)));

    $this->getLogService()->info('user', 'change_role', "设置用户{$user['nickname']}(#{$user['id']})的角色为：".implode(',', $roles));
}
```