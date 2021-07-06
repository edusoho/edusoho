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
  {
    // 课程、班级、题库练习错题展示
    name: 'getWrongBooksQuestionShow',
    url: '/wrong_books/{poolId}/question_show',
    method: 'GET',
  },
  {
    // 错题课程分类级联查询条件
    name: 'getWrongQuestionCondition',
    url: '/wrong_books/{poolId}/condition',
    method: 'GET',
  },
];
