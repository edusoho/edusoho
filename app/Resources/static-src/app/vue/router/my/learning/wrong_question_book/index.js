export default [
  {
    path: '/',
    name: 'WrongQuestionBook',
    component: () => import(/* webpackChunkName: "app/vue/dist/WrongQuestionBook" */ 'app/vue/views/my/learning/wrong_question_book/index.vue')
  }
];
