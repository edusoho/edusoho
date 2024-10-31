import {apiClient, ajaxClient} from '../api-client';

export default {
  async search(classroomId, params) {
    return apiClient.get(`/classroom/${classroomId}/members`, {params});
  },
  async remove(classroomId, userId) {
    return ajaxClient.post(`/classroom/${classroomId}/manage/student/${userId}/remove`);
  },
  async batchRemove(classroomId, studentIds) {
    return ajaxClient.post(`/classroom/${classroomId}/manage/student/remove`, new URLSearchParams({studentIds}));
  },
  async export(classroomId, params) {
    return ajaxClient.get(`/classroom/${classroomId}/manage/student/export/student/datas`, {params});
  },
};
