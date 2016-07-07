# 云服务IM API

## 获取登录im的token

```
GET /im/login
```

### 参数

| 名称 | 类型 | 必需 | 描述 |
| ---- | ---- | ---- | ---- |

### 成功响应

```json
{
    "servers": ["ws://127.0.0.1:10000/chatRot?token=1:1:1457406838:desktop:f05d7515a1d8d8295b51608d67425fce",...],
}
```

### 失败响应

```json
{
    "error": "xxxx",
    "code": "xxx",
}
```