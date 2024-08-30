export default [
  {
    name: 'getSignContractTemplate',
    url: '/contract/{id}/sign/{goodsKey}',
    method: 'GET'
  },
  {
    name: 'getSignedContractDetail',
    url: '/signed_contract/{id}',
    method: 'GET',
  },
  {
    name: 'getContractById',
    url: '/contract/{id}',
    method: 'GET',
  },
  {
    name: 'signContract',
    url: '/contract/{id}/sign',
    method: 'POST',
  }
]
