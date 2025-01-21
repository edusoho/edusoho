import {ajaxClient} from '../api-client';

export default {
  async updateClassroom(classroomId, params) {
    return ajaxClient.post(`/classroom/${classroomId}/manage/set_info`, new URLSearchParams(params));
  },
};