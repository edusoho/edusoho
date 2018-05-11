# 开启cdn, 教育云用
# 参数解释 
#   api verison: 标记版本，api分为3个版本，目前只支持用第3个版本
#   api url: api 路由
#   api method: 支持 PUT, GET, POST, PATCH 4种，目前只支持api 3.0
#   api login: 有此属性的api，需要登录才能访问，即要带token
#   api-url-editable: 值为true时，通过在上面的api-url 输入框内输入url, 可修改路由
#   api authorized: 有此属性的api，一般会加上标签 @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN") 

api-version: 3
api-url: /api/cdns
api-method: POST
api-authorized: true
api-login: false

{
    "enabled": "1",    // 必填，0 或 1
    "default_url": "//sce2a3b1c3d2n9-sb.edusoho.net/",  //可不填， 一般enabled为0时，才不填
    "user_url": "//sce2a3b1c3d2n9-sb.edusoho.net/",     //非必填
    "content_url": "//sce2a3b1c3d2n9-sb.edusoho.net/"   //非必填
}