import activityRequest from './request';

export default {
  methods: {
    // addTicket 存在时表示需要重新拼接ticket参数（微营销分享中需要）
    activityHandle(activityId, callback, addTicket = false) {
      activityRequest(activityId, callback, addTicket);
    }
  }
};
