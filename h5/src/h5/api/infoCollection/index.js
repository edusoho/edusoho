export default [
  {
    // 提交表单
    name: 'setInfoCollection',
    url: '/information_collect_form',
    method: 'post',
    disableLoading: true,
  },
  {
    // 根据事件id获取表单
    name: 'getInfoCollectionForm',
    url: '/information_collect_form/{eventId}',
    method: 'get',
    disableLoading: true,
  },
  {
    // 根据购买前后获取表单
    name: 'getInfoCollectionEvent',
    url: '/information_collect_event/{action}',
    method: 'get',
    disableLoading: true,
  },
];
