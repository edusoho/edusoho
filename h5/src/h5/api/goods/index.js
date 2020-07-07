export default [
  {
    // 课程信息
    name: 'getGoodsCourse',
    url: '/goods/{id}',
    method: 'GET',
    disableLoading: true
  },
  {
    // 课程商品页的组件信息
    name: 'getGoodsCourseComponents',
    url: '/goods/{id}/components',
    method: 'GET',
    disableLoading: true
  },
  {
    // 添加收藏
    name: 'addFavorite',
    url: '/favorite',
    method: 'POST',
    disableLoading: true
  },
  {
    // 移除收藏
    name: 'removeFavorite',
    url: '/favorite',
    method: 'DELETE',
    disableLoading: true
  }
];
