# 上报页面停留时长（学习时间）及观看时长（音视频有观看时长）

api-version: 3
api-url: /api/courses/1/tasks/1/events/doing
api-method: PATCH
api-authorized: false
api-login: true
api-url-editable: true

{
    "lastTime": "1536148523", // 开始停留页面的时间，每次上传后，需要重新计算开始停留页面的时间
    "watchTime": "10"// 单位为秒，观看时间，视频和音频的额外属性
}