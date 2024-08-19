import { apiClient } from '../apiClient'

export const ElectronicContract = {
  getContractById: ({ id }) => {
    return apiClient.get(`/api/contract/${id}`)
  },

  getContractList: () => {
    return apiClient.get(`/api/contract`)
  },

  signContract: () => {
    return apiClient.get(`/api/contract/${id}/sign`)
  }
}