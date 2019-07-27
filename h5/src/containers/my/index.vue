<template>
  <div>
    <e-loading v-if="isLoading"></e-loading>
    <user></user>
    <router-link :to="{name: 'couponCovert'}">
      <div class="coupon-code-entrance" v-if="hasBusinessDrainage">兑换卡券
        <i class="van-icon van-icon-arrow pull-right"></i>
      </div>
    </router-link>
    <van-tabs v-model="activeIndex" class="after-tabs e-learn">
      <van-tab v-for="(item, index) in tabs"  :title="item" :key="index"></van-tab>
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
import Api from '@/api';

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
      hasBusinessDrainage: false
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
    Api.hasPluginInstalled().then(res => {
      this.hasBusinessDrainage = res.BusinessDrainage
    })
  }
}
</script>

