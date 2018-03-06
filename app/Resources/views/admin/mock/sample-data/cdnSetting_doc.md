# 开启cdn, 教育云用
# 参数解释 
#   api verison: 标记版本，api分为3个版本，目前只支持用第3个版本
#   api url: api 路由
#   api method: 支持 PUT, GET, POST, PATCH 4种，目前只支持api 3.0
#   api authorized: 有此属性的api，一般会加上标签 @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN") 

api-version: 3
api-url: /api/cdnSettings
api-method: POST
api-authorized: true

{
    "enabled": "1",    // 必填，0 或 1
    "default_url": "//sce2a3b1c3d2n9-sb.edusoho.net/",  //可不填， 一般为0时，才不填
    "user_url": "//sce2a3b1c3d2n9-sb.edusoho.net/",     //非必填
    "content_url": "//sce2a3b1c3d2n9-sb.edusoho.net/"   //非必填
}