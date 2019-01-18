import Api from '@/api';
import { Toast } from 'vant';

export default {
  methods: {
    activityHandle(activityId, addTicket = false) {
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
        const url = addTicket ? `${res.url}${symbol}ticket=${res.ticket}` : res.url;
        window.location.href = url;
      }).catch(err => {
        Toast.fail(err.message);
      });
    }
  }
};
