# 密码安全测试用例

## 一、密码强度要求

* 学员账号：8-32位字符，包含字母、数字、符号任意两种及以上组合成的密码
* 员工账号：8-32位字符，包含字母大小写、数字、符号四种字符组合成的密码

## 二、登录

### 2.1 学员身份，账号密码/手机快捷登录

#### 2.1.1 后台开启【学员登录时，弱密码检测】的情况下：

* 学员的密码强度弱，跳转到重设密码流程。
* 学员的密码强度达标，提示登录成功。
* 学员的密码强度达到员工密码强度，提示登录成功。

#### 2.1.2 后台关闭【学员登录时，弱密码检测】的情况下：

* 学员的密码强度弱，提示登录成功。
* 学员的密码强度刚好，提示登录成功。
* 学员的密码强度达到员工密码强度要求，提示登录成功。

### 2.2 员工身份，账号密码/手机快捷登录

* 员工的密码强度弱，跳转到重设密码流程。
* 员工的密码等级达到学员的密码等级，跳转到重设密码流程。
* 员工的密码强度达标，提示登录成功。

### 2.3 员工刷新网校页面

* 如果密码等级不达标，会退出登录。再次登录时，会调试密码等级低，并跳转到密码重设流程。

## 三、注册

### 3.1 手机注册

* 达到学员账号密码等级，即可成功注册。

### 3.2 邮箱注册

* 达到学员账号密码等级，即可成功注册。

## 四、第三方登录注册

TODO.

## 五、找回密码

### 5.1 通过手机号找回密码

注意：界面上显示的密码要求始终是学员账号的要求。体验更好一点的是，能根据输入的手机号判断账号类型给出不同的强度要求。（下个迭代用 VUE 重写后改进）

#### 5.1.1 学员手机号找回密码

* 新密码达到学员账号密码等级，即可找回成功。

#### 5.1.2 员工手机号找回密码

* 新密码达到学员账号密码等级，即可点击重设密码。但后端会提示“您是员工账号，需达到员工的账号密码等级”。
* 新密码达到员工账号密码等级，即可找回成功。

### 5.2 通过邮箱找回密码

#### 5.2.1 学员邮箱找回密码

* 新密码达到学员账号密码等级，即可找回成功。

#### 5.2.2 员工邮箱找回密码

* 新密码达到员工账号密码等级，即可找回成功。

### 5.3 个人设置->安全设置->密码修改

#### 5.3.1 学员修改密码

* 新密码达到学员账号密码等级，即可修改成功。

#### 5.3.2 员工修改密码

* 新密码达到员工账号密码等级，即可修改成功。

## 六、管理后台

### 6.1 添加学员

* 注册模式为【手机注册时】，添加用户，密码达到学员账号密码登记，即可添加成功。
* 注册模式为【邮箱注册时】，添加用户，密码达到学员账号密码等级，即可添加成功。
* 注册模式为【手机和邮箱注册时】，添加用户，密码达到学员账号密码等级，即可添加成功。

### 6.2 批量导入学员

* 批量导入的账号密码，达到员工账号密码等级，即可导入成功。

### 6.3 添加员工

* 注册模式为【手机注册时】，添加员工，密码达到员工账号密码登记，即可添加成功。
* 注册模式为【邮箱注册时】，添加员工，密码达到员工账号密码等级，即可添加成功。
* 注册模式为【手机和邮箱注册时】，添加员工，密码达到员工账号密码等级，即可添加成功。

### 6.4 修改学员密码

* 新密码达到学员账号密码等级，即可修改成功。

### 6.5 修改员工密码

* 新密码达到员工账号密码等级，即可修改成功。

## 七、系统升级

* 通过后台升级系统到最新版后，【学员登录时，弱密码检测】开关默认关闭。

## 八、H5/App端

* 参考 PC 端的规则。
* 所有客户端写死的密码提示都修改为“8-32位字符，包含字母、数字、符号任意两种及以上组合成的密码”，不区分学员/员工；
* 员工在H5/App端重置密码时，后端还是会按员工的要求校验，不通过时会提示“您是员工账号，密码等级需达到8-32位字符，包含字母大小写、数字、符号四种字符组合成的密码”。

## 重置密码 SQL

重置密码为弱密码`kaifazhe`：
```sql
UPDATE user SET password = '8zPhPj8e02iM+ZKvtELdL+4kh9A1aA+QinEAXoCTW7I=', salt = 'hkwo2pk9atc0s4gosk4sggcc8wko44o', passwordUpgraded = '0' WHERE nickname = '李雷' LIMIT 1;
UPDATE user SET password = '8zPhPj8e02iM+ZKvtELdL+4kh9A1aA+QinEAXoCTW7I=', salt = 'hkwo2pk9atc0s4gosk4sggcc8wko44o', passwordUpgraded = '0' WHERE nickname = '管理员' LIMIT 1;
```

重置密码为学员等级`Kaifazhe00`:
```sql
UPDATE user SET password = 'KdpOIVZgl/Pb7NZGEzZNQaq8daibtcUtBgm+D2IJ6W8=', salt = '73hkhdvmo5wc4w4sso040s0ssksksgc', passwordUpgraded = '0' WHERE nickname = '李雷' LIMIT 1;
UPDATE user SET password = 'KdpOIVZgl/Pb7NZGEzZNQaq8daibtcUtBgm+D2IJ6W8=', salt = '73hkhdvmo5wc4w4sso040s0ssksksgc', passwordUpgraded = '0' WHERE nickname = '管理员' LIMIT 1;
```

重置密码为员工等级`Kaifazhe00@@`:
```sql
UPDATE user SET password = 'LxjB1mpxx1Zwj6HudDJfywRZlCLzDZFJygpNgbSoc0E=', salt = 'nsur7jh5snkc0wskkk8oscwwkwckkos', passwordUpgraded = '0' WHERE nickname = '李雷' LIMIT 1;
UPDATE user SET password = 'LxjB1mpxx1Zwj6HudDJfywRZlCLzDZFJygpNgbSoc0E=', salt = 'nsur7jh5snkc0wskkk8oscwwkwckkos', passwordUpgraded = '0' WHERE nickname = '管理员' LIMIT 1;
UPDATE user SET password = 'LxjB1mpxx1Zwj6HudDJfywRZlCLzDZFJygpNgbSoc0E=', salt = 'nsur7jh5snkc0wskkk8oscwwkwckkos', passwordUpgraded = '0' WHERE nickname = '老师' LIMIT 1;
```