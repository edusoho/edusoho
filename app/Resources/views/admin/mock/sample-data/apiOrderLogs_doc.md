# 某个订单下的所有订单日志
# 参数解释 
#   api verison: 标记版本，api分为3个版本，目前只支持用第3个版本
#   api url: api 路由
#   api method: 支持 PUT, GET, POST, PATCH 4种，目前只支持api 3.0
#   api login: 有此属性的api，需要登录才能访问，即要带token
#   api-url-editable: 值为true时，通过在上面的api-url 输入框内输入url, 可修改路由
#   api authorized: 有此属性的api，一般会加上标签 @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN") 

api-version: 3
api-url: /api/order/1/logs
api-method: GET
api-authorized: true
api-login: false
api-url-editable: true

{
    //无任何查询条件
}