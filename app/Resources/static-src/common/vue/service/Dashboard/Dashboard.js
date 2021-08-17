import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'

const baseUrl = '/api/dashboard_graphic_datum';
const baseService = new BaseService({ baseUrl })

export const Dashboard = _.assignIn(baseService, {

})