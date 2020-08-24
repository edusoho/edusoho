export default [
  {
    // 已获取证书详情
    name: 'certificateRecords',
    url: '/certificate_records/{certificateRecordId}',
    method: 'GET',
  },
  {
    // 我的证书
    name: 'meCertificate',
    url: '/me/certificate_records',
    method: 'GET',
  },
  {
    // 获取证书列表
    name: 'certificates',
    url: '/certificates',
    method: 'GET',
  },
  {
    // 获取证书详情
    name: 'certificatesDetail',
    url: '/certificates/{certificateId}',
    method: 'GET',
  },
  {
    // 下载证书
    name: 'certificateDownload',
    url: '/certificate/image/{recordId}/download',
    method: 'GET',
  },
];
