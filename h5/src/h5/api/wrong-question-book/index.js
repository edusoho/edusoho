export default [
  {
    // 我的错题列表信息
    name: 'getWrongBooks',
    url: '/me/wrong_books',
    method: 'GET',
  },
  {
    // 我的错题题目分类
    name: 'getWrongBooksCertainTypes',
    url: '/me/wrong_books/{targetType}/certain_types',
    method: 'GET',
  },
];
