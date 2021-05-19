import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/course_set';
const baseService = new BaseService({ baseUrl })

export const CourseSet = {
  ...baseService
}