import { apiClient } from '../apiClient'

export const ElectronicContract = {
  getContractById: ({ id }) => {
    return apiClient.get(`/api/contract/${id}`)
  },

  getContractList: () => {
    return apiClient.get(`/api/contract`)
  },

  signContract: ({ id, contractCode, goodsKey, truename, IDNumber, phoneNumber, handSignature }) => {
    return apiClient.post(`/api/contract/${id}/sign`, { contractCode, goodsKey, truename, IDNumber, phoneNumber, handSignature })
  },

  getSignContractTemplate: ({ id, goodsKey }) => {
    return apiClient.get(`/api/contract/${id}/sign/${goodsKey}`)
  }
}