import {ajaxClient, apiClient} from '../api-client';

export default {
    async getCategory(type) {
        return ajaxClient.get(`/category/choices/${type}`);
    },
    async getCategories(type) {
        return apiClient.get(`/api/categories/${type}`);
    },
};