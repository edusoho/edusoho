export default [
  {
    // 获取直播地址
    name: 'getLiveUrl',
    url: '/lessons/{taskId}/live_tickets/{no}',
    method: 'GET'
  }, {
    // 获取录播地址
    name: 'getLiveReplayUrl',
    url: '/lessons/{taskId}/replay',
    method: 'GET'
  }, {
    // 获取直播No
    name: 'requestLiveNo',
    url: '/lessons/{taskId}/live_tickets',
    method: 'POST'
  }, {
    // 获得考试说明信息
    name: 'testpaperIntro',
    url: '/testpaper_infos/{testId}',
    method: 'GET'
  }, {
    // 获得考试成绩
    name: 'testpaperResult',
    url: '/testpaper_results/{resultId}',
    method: 'GET'
  }

];
