import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/course';

export const Course = {
  // 获取课程教师
  async getTeacher(courseId, params) {
    return apiClient.get(`${baseUrl}/${courseId}/member`, params)
  },

  // 获取班课课时列表
  async getCourseLesson(courseId, params) {
    return apiClient.get(`${baseUrl}/${courseId}/item_with_lesson_v2`, { params })
  },

  // 课时列表排序
  async courseSort(courseId, params) {
    return apiClient.post(`${baseUrl}/${courseId}/item_sort`, params)
  },

  // 删除课时
  async deleteTask(courseId, taskId) {
    return apiClient.delete(`${baseUrl}/${courseId}/task/${taskId}`)
  },

  // 更新课时状态
  async updateTaskStatus(courseId, taskId, params) {
    return apiClient.patch(`${baseUrl}/${courseId}/task_status/${taskId}`, params)
  },

  async getSingleCourse(courseId) {
    return apiClient.get(`${baseUrl}/${courseId}`)
  },

  // 新增章节
  async addChapter(courseId, params) {
    return apiClient.post(`${baseUrl}/${courseId}/chapter`, params)
  },

  // 新增直播课时（包括批量）
  async addLiveTask(courseId, params) {
    return apiClient.post(`${baseUrl}/${courseId}/live_task`, params)
  }
}
