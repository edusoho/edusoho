import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/wrong_book';

export const WrongBookQuestionShow = {
  // 课程、班级、题库练习错题展示
  async search(apiParams) {
    let params = apiParams.params;
    return apiClient.get(`${baseUrl}/${apiParams.query.poolId}/question_show`, { params });
  }
}
