import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService';
const baseUrl = '/api/assistant_permission';
const baseService = new BaseService({ baseUrl })

export const AssistantPermission = _.assignIn(baseService, {

})
