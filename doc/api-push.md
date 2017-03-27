# 面向App的消息推送相关接口


## 接口：get /api/im/sync

#### 接口说明：

	- 由移动端主动请求，用于更新当前用户所属的课程、班级的会话，需新增的新增，需加入的加入，需退出的退出；
	- 接口鉴权方式：X-Auth-Token: {token}, token通过 api/users/login接口获取；

#### 返回值：

成功：
```json
{
  "convNo": "d8511674b5421d071d9598bd5fd25463"
}
```

失败：
```json
{
    "error": "xxxx",
    "code": "xxx",
}
```

#### 测试方式

以Postman为例：

	- 请求 POST /api/users/login 附件消息体(Body)：nickname={username}, password={password} 获取用户的token。
	- 请求 GET /api/im/sync 附加头部(Header): X-Auth-Token: {token}，检查响应是否成功。


## 推送： 直播前十分钟提醒

#### 推送说明

教师创建直播课时并发布，在课时开始前十分钟，本课程的学员如果启用了App，将在App端收到直播即将开始的通知，大致文案如下：
```
您报名的课程xxx，即将于19:30开始直播，马上前往直播教室准备学习吧!
```