import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/courses';

export const Courses = {
  // 删除课时
  async deleteTask(courseId, taskId) {
    return apiClient.delete(`${baseUrl}/${courseId}/task/${taskId}`)
  },

  // 更新课时状态
  async updateTaskStatus(courseId, taskId, params) {
    return apiClient.patch(`${baseUrl}/${courseId}/task_status/${taskId}`, params)
  }
}
