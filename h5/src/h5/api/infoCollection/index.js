export default [
  {
    // 提交表单
    name: 'setInfoCollection',
    url: '//api/information_collect_form',
    method: 'post',
  },
  {
    // 根据事件id获取表单
    name: 'getInfoCollectionForm',
    url: '//information_collect_form/{eventId}',
    method: 'get',
  },
  {
    // 根据购买前后获取表单
    name: 'getInfoCollectionEvent',
    url: '/information_collect_event/{action}',
    method: 'get',
  },
];
