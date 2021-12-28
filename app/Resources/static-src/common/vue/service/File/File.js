import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/file';

export const File = _.assignIn({
  async uploadFile(params) {
    return apiClient.post('/file/upload', params)
  },
  async imgCrop(params) {
    return apiClient.post('/file/img/crop', params)
  },

  async file(params) {
    return apiClient.post('/api/file', params)
  }
})
