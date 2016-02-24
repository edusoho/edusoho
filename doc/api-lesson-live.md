# Lesson Live API

## 创建票据

```
POST /lessons/{id}/live_tickets
```

### 参数

| 名称 | 类型 | 必需 | 描述 |
| ---- | ---- | ---- | ---- |
| id   | string | Y | 用户ID |
| nickname | string | Y | 用户昵称 |
| role | string | Y | 角色（teacher,student,speaker) |
| device | string | Y | 设备(desktop, mobile, android, ios) |

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