import { apiClient } from './api-client.js';

export default class ApiFactory {
  constructor(props) {
    this.baseUrl = props.baseUrl;
  }

  async add(params) {
    return apiClient.post(this.baseUrl, params)
  }

  async update(params) {
    return apiClient.put(`${this.baseUrl}/${id}`, params)
  }

  async search(params) {
    return apiClient.get(this.baseUrl, params)
  }
  
  async delete({ id }) {
    return apiClient.delete(`${this.baseUrl}/${id}`)
  }

  changeBaseUrl(url) {
    this.baseUrl = url
  }
}