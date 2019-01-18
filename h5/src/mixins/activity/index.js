import Api from '@/api';

export default {
  methods: {
    activityHandle(activityId) {
      console.log('activityId', activityId, location.origin);
      const params = {
        domainUri: 'http://lvliujie.st.edusoho.cn',
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
