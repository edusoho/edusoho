# 参数解释
#   api-verison: 标记版本，api分为3个版本，目前只支持用第3个版本
#   api-url: api 路由
#   api-method: 支持 PUT, GET, POST, PATCH 4种，目前只支持api 3.0
   patch 需要额外加上header Content-Type: application/json-patch+json，
   同时使用postman时，需要将参数放入 raw里面，以json的方式存在
#   api-login: 有此属性的api，需要登录才能访问，即要带token, 
    本请求支持自动携带token, 需要填api-user-id输入框
#   api-url-editable: 值为true时，通过在上面的api-url 输入框内输入url, 可修改路由
#   api-authorized: 有此属性的api，一般会加上标签 @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")


