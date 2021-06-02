import { apiClient } from 'common/vue/service/api-client.js';

export const CourseItemWithLessonV2 = {
  async search(params) {
    return apiClient.get(`/api/course/${params.id}/item_with_lesson_v2`, params)
  }
}
