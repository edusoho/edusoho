import {apiClient} from '../api-client';

export default {
    async setCategory(params) {
        return apiClient.post('/upload_file_category', params);
    },
};