import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'

const baseUrl = '/api/setting';
const baseService = new BaseService({ baseUrl })

export const Setting = _.assignIn(baseService, {

})
