export default [
  {
    // 保存
    name: 'saveAnswer',
    url: '/save_answer',
    method: 'POST',
  },
  {
    // 提交
    name: 'submitAnswer',
    url: '/submit_answer',
    method: 'POST',
  },
  {
    // 继续答题
    name: 'continueAnswer',
    url: '/continue_answer',
    method: 'POST',
  },
  {
    // 答题报告
    name: 'answerRecord',
    url: '/answer_record/{answerRecordId}',
  },
];
