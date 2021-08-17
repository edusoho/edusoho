import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'

const baseUrl = '/api/dashboard_rank_list';
const baseService = new BaseService({ baseUrl })

export const DashboardRank = _.assignIn(baseService, {

})