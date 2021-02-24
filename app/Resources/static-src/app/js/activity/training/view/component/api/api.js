import axios from 'axios'

export default {
  //容器申请 返回id
  labApply(domain_name, param) {
    return axios.create({baseURL: domain_name}).post(`lab/create/` + param.course_id + `/` + param.subsec_id);
  },
  //容器申请是否成功 返回数据
  labQuery(domain_name, param) {
    return axios.create({baseURL: domain_name}).get(`lab/query/` + param.lab_id)
  },
  //每10秒lab容器至少有一次心跳，否在服务会检测到容器在1分钟之内没有心跳，并将其销毁操作
  labHeartbeat(domain_name, param) {
    return axios.create({baseURL: domain_name}).post(`lab/heartbeat/` + param.lab_id, param)
  },
  //容器执行销毁接口
  labDelete(domain_name, param) {
    return axios.create({baseURL: domain_name}).delete(`lab/destroy/` + param.lab_id)
  },
  // 查询query返回address是否可用
  labStatus(url) {
    return axios.get(url)
  }
}