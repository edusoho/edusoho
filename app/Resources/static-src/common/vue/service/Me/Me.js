import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'
const baseUrl = '/api/me';

const baseService = new BaseService({ baseUrl })

export const Me = _.assignIn(baseService, {

});
