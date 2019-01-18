import Api from '@/api';

export default {
  methods: {
    activityHandle(activityId) {
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
        window.location.href = res.url;
      }).catch(err => {
        console.log(err.message);
      });
    }
  }
};
