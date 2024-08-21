import { apiClient } from '../apiClient'

export const ElectronicContract = {
  getContractById: ({ id }) => {
    return apiClient.get(`/api/contract/${id}`)
  },

  getContractList: () => {
    return apiClient.get(`/api/contract`)
  },

<<<<<<< HEAD
  signContract: ({ id, contractCode, goodsKey, truename, IDNumber, phoneNumber, handSignature }) => {
    return apiClient.get(`/api/contract/${id}/sign`)
  },

  getSignContractTemplate: ({ id, goodsKey }) => {
    return apiClient.get(`/api/contract/${id}/sign/${goodsKey}`)
  }
}

=======
  signContract: () => {
    return apiClient.get(`/api/contract/${id}/sign`)
  }
}
>>>>>>> feat/20240805-wb
