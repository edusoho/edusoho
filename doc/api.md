# EduSoho API

## 授权认证方式

  * Token认证
    HTTP Header 中设置：
    ```
    X-EduSoho-Auth-Method: token
    X-EduSoho-Auth-Token: ${TOKEN}
    ```
    `${TOKEN}`的值来自调用`/users/login`接口。

  * Key认证
    HTTP Header 中设置：
    ```
    X-EduSoho-Auth-Method: key
    X-EduSoho-Auth-Key: ${KEY}
    X-EduSoho-Auth-Sign: ${SIGN} 
    ```
    `${KEY}`即AccessKey，`${SIGN}`为本次请求的签名

## 响应类型

X-QiQiuYun-Document: V1User