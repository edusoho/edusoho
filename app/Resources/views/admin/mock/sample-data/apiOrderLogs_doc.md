# 某个订单下的所有订单日志
# 参数解释 
#   api verison: 标记版本，api分为3个版本，目前只支持用第3个版本
#   api url: api 路由
#   api method: 支持 PUT, GET, POST, PATCH 4种，目前只支持api 3.0
#   api login: 有此属性的api，需要登录才能访问，即要带token
#   api authorized: 有此属性的api，一般会加上标签 @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN") 

api-version: 3
api-url: /api/order/1/logs
api-method: GET
api-authorized: true
api-login: false

{
    //无任何查询条件
}