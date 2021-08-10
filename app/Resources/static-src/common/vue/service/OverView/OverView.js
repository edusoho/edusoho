import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'

const baseUrl = '/api/timeout_review';
const baseService = new BaseService({ baseUrl })

export const OverView = _.assignIn(baseService, {

})