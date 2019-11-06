# 获取计划下的目录结构（包括课时）

api-version: 3
api-url: /api/courses/1/item_with_lessons
api-method: GET
api-authorized: false
api-login: true
api-url-editable: true

{
    "onlyPublished": "1",  // 是否只显示发布课时
    "fetchSubtitlesUrls" : "1" //是否获取字幕
}