import Api from '@/api';
import { Toast } from 'vant';

// addTicket 存在时表示需要重新拼接ticket参数（微营销分享中需要）
const activityHandle = (activityId, callback, addTicket = false) => {
  if (!activityId || (addTicket && !callback)) {
    Toast.fail('缺少分享参数');
    console.error('缺少分享参数 activityId 或 callback');
    return;
  }
  const params = {
    domainUri: location.origin,
    itemUri: '',
    source: 'h5'
  };
  Api.marketingActivities({
    query: {
      activityId
    },
    data: params
  }).then(res => {
    const symbol = res.url.indexOf('?') !== -1 ? '&' : '?';
    const url = addTicket ? `${callback}${symbol}ticket=${res.ticket}` : res.url;
    window.location.href = url;
  }).catch(err => {
    Toast.fail(err.message);
  });
};

export default activityHandle;
