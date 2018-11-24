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
  }
];
