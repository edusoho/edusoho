# 开启cdn, 教育云用

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