import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'

const baseUrl = '/api/course_set';
const baseService = new BaseService({ baseUrl })

export const CourseSet = _.assignIn(baseService, {

})