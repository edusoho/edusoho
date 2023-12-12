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
  {
    // 获取附件信息
    name: 'getItemDetail',
    url: '/item_detail',
    disableLoading: true,
  },
  {
    // 单题提交
    name:'submitSingleAnswer',
    url: '/answer_record/{id}/submit_single_answer',
    disableLoading: true,
    method: 'POST',
  },
  {
    // 结束答题
    name:'finishAnswer',
    url: '/answer_record/{id}/finish_answer',
    disableLoading: true,
    method: 'POST',
  },
	{
		// 单题提交
		name: 'singleQuestionSubmission',
		url: '/answer_record/{id}/review_single_answer',
		disableLoading: true,
    method: 'POST',
	}
];
