import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/file';

export const File = _.assignIn({
  async uploadFile(params) {
    return apiClient.post('/file/upload', params)
  },
  async ImgCrop(params) {
    return apiClient.post('/file/img/crop', params)
  }
})
