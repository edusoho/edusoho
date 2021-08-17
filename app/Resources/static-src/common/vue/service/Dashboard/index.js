import { apiClient } from 'common/vue/service/api-client.js';

export default {
	async searchGraphicDatum ({ query = {}, params = {}, data = {} } = {}) {
		return await apiClient.get(`/api/dashboard_graphic_datum`, { params })
	},

	async searchRankList ({ query = {}, params = {}, data = {} } = {}) {
		return await apiClient.get(`/api/dashboard_rank_list`, { params })
	}
}