# Lesson Live API

## 创建票据

```
POST /lessons/{id}/live_tickets
```

### 参数

| 名称 | 类型 | 必需 | 描述 |
| ---- | ---- | ---- | ---- |
| device | string | Y | 设备(desktop, mobile, android, iphone) |

### 响应

```json
{
    "no": "56cbfe3fa5aa8737384577"
    "roomId": "19759"
    "user": {
        "id": "2"
        "nickname": "测试管理员"
        "role": "student"
        "user": "2"
    }
    "device": "desktop"
}
```

## 得到票据

```
GET /lessons/{id}/live_tickets/{ticket}
```

票据未完成时响应：
```json
{
    "no": "56cbfe3fa5aa8737384577"
    "roomId": "19759"
    "user": {
        "id": "2"
        "nickname": "测试管理员"
        "role": "student"
        "user": "2"
    }
    "device": "desktop"
}
```

票据完成时响应：

Html 5:
```json
{
    "no": "56cbfe3fa5aa8737384577"
    "roomId": "19759"
    "user": {
        "id": "2"
        "nickname": "测试管理员"
        "role": "student"
        "user": "2"
    }
    "device": "desktop"
    "roomUrl": "http://xxx.com/xxxxxxxx"
}
```

SDK:
```json
{
    "no": "56cbfe3fa5aa8737384577"
    "roomId": "19759"
    "user": {
        "id": "2"
        "nickname": "测试管理员"
        "role": "student"
        "user": "2"
    }
    "device": "android",
    "sdk": {
        "provider": "soooner",
        ....
    }
}
```


## 获取回放地址

```
GET /lessons/{id}/replay
```

### 参数

| 名称 | 类型 | 必需 | 描述 |
| ---- | ---- | ---- | ---- |
| replayId | string | Y | 回放ID |
| device | string | Y | 设备(desktop, mobile, android, iphone) |

```json
{
    "device": "",
    "url": "",
    "sdk": {
        "provider": "soooner",
        "liveClassroomId": "",
        "exStr": "",
    },
    "nonsupport": true
}
```

目前sdk，只会出现在光慧直播，且请求参数`device`为`android`或`iphone`) 存在。

如果不支持在当前设备播放，就会出现`nonsupport`，比如三芒的回放暂不支持在移动端播放。

```





