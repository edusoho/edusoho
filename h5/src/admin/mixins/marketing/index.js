import { mapState } from 'vuex';

// 微营销创建活动地址
export default {
  computed: {
    ...mapState(['createMarketingUrl']),
  },
};
