import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/wrong_book';

export const WrongBookStudentWrongQuestion = {
  // 教师查看学员错题列表
  async get(apiParams) {
    const params = apiParams.params;
    return apiClient.get(`${baseUrl}/${apiParams.query.targetId}/student/${apiParams.query.targetType}/wrong_question`, { params });
  }
}
