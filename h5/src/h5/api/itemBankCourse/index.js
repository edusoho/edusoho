export default [
  {
    // 获取题库课程列表数据
    name: 'getItemBankList',
    url: '/item_bank_exercises',
  },
  {
    // 获取题库课程信息
    name: 'getItemBankExercise',
    url: '/item_bank_exercises/{id}',
  },
  {
    // 获取题库目录信息
    name: 'getItemBankModules',
    url: '/item_bank_exercises/{id}/modules',
  },
  {
    // 获取题库试卷信息
    name: 'getItemBankAssessments',
    url: '/item_bank_exercises/{exerciseId}/modules/{moduleId}/assessments',
    disableLoading: true,
  },
  {
    // 获取题库章节信息
    name: 'gettemBankCategories',
    url: '/item_bank_exercises/{exerciseId}/modules/{moduleId}/categories',
    disableLoading: true,
  },
];
