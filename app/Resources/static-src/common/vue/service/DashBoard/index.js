import { apiClient } from 'common/vue/service/api-client.js';

export default {
	searchGraphicDatum ({ query = {}, params = {}, data = {} } = {}) {
		return apiClient.get(`/api/dashboard_graphic_datum`, { params });
	},

	searchRankList ({ query = {}, params = {}, data = {} } = {}) {
		return apiClient.get(`/api/dashboard_rank_list`, { params });
	}
}