<template>
  <div>
    <e-loading v-if="isLoading"></e-loading>
    <user></user>

    <van-tabs v-model="activeIndex" class="after-tabs e-learn">
      <van-tab v-for="(item, index) in tabs" :title="item" :key="index"></van-tab>
    </van-tabs>
    <orders v-show="activeIndex === 0"></orders>
    <activity v-show="activeIndex === 1"></activity>
  </div>
</template>
<script>
import Orders from '../order/orders.vue';
import activity from './activity';
import User from './user.vue';
import { mapState } from 'vuex';
import preloginMixin from '@/mixins/preLogin';

export default {
  mixins: [preloginMixin],
  components: {
    Orders,
    activity,
    User
  },
  data() {
    return {
      activeIndex: 0,
      tabs: ['我的订单', '我的活动'],
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
  },
  created() {
    setTimeout(() => {
      window.scrollTo(0,0);
    }, 100)
  }
}
</script>

