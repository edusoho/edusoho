# 获取计划下的目录结构（不包括课时）

api-version: 3
api-url: /api/courses/1/items
api-method: GET
api-authorized: false
api-login: true
api-url-editable: true

{
    "onlyPublished": "1"  // 是否只显示发布课时,
    "fetchSubtitlesUrls" : "1" //是否获取字幕
}