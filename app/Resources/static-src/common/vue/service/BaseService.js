import { apiClient } from './api-client.js';

export default class BaseService {
  constructor(props) {
    this.baseUrl = props.baseUrl || '';
  }

  async get(target, params) {
    return apiClient.get(`${this.baseUrl}/${target}`, params)
  }

  async add(params) {
    return apiClient.post(this.baseUrl, params)
  }

  async update(params) {
    return apiClient.put(`${this.baseUrl}/${params.id}`, params)
  }

  async search(params) {
    return apiClient.get(this.baseUrl, { params })
  }

  async delete({ id }) {
    return apiClient.delete(`${this.baseUrl}/${id}`)
  }

  async edit(params) {
    return apiClient.post(this.baseUrl, params)
  }

  changeBaseUrl(url) {
    this.baseUrl = url
  }
}
