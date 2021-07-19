import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/wrong_book';

export const WrongBookWrongQuestionDetail = {
  // 教师查看学员错题详情
  async get(apiParams) {
    const params = apiParams.params;
    return apiClient.get(`${baseUrl}/${apiParams.query.targetType}/wrong_question/${apiParams.query.itemId}/detail`, { params });
  }
}
