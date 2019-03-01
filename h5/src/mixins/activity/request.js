import Api from '@/api';
import { Toast, Dialog } from 'vant';
import clipboard from '@/utils/clipboard';

const isWeixinBrowser = /micromessenger/.test(navigator.userAgent.toLowerCase());

// addTicket 存在时表示需要重新拼接ticket参数（微营销分享中需要）
const activityHandle = (activityId, callback, addTicket = false) => {
  if (!activityId || (addTicket && !callback)) {
    Toast.fail('缺少分享参数');
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
    if (!isWeixinBrowser) {
      Dialog.confirm({
        message: '去微信完成活动', // 请在微信客户端打开链接
        confirmButtonText: '复制链接',
        title: ''
      }).then(() => {
        try {
          clipboard(url, () => {
            Toast.success('复制成功');
          });
        } catch (err) {
          Toast.fail('请更换浏览器复制');
        }
      }).catch(() => {});
      return;
    }
    window.location.href = url;
  }).catch(err => {
    Toast.fail(err.message);
  });
};

export default activityHandle;
