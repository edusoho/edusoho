export default [
  {
    path: '/',
    name: 'WrongQuestionBookDetail',
    component: () => import(/* webpackChunkName: "app/vue/dist/WrongQuestionBookDetail" */ 'app/vue/views/my/learning/wrong_question_book/detail/index.vue')
  }
];
