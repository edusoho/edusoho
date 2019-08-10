<template>
  <div class="coupon-receive-page">
    <div :class="['coupon-card-bg', 'clearfix', couponStatus]">
      <div class="coupon-info">{{ message }}</div>
      <div class="e-coupon__body" v-if="Object.keys(item).length">
        <div class="e-coupon__header clearfix">
          <span class="e-coupon__price" v-html="priceHtml(item)"></span>
          <div class="e-coupon__name">
            <div class="text-overflow text-14 coupon-name">{{ item.name }}</div>
             <!-- 兼容老版本优惠券无有效期功能或者非有效期模式-->
              <span v-if="!item.deadlineMode || item.deadlineMode==='time'" class="text-10">{{ timeExpire(item.createdTime,item.deadline) }}</span>
            <!-- 新版优惠券功能 -->
              <!-- 非有效期模式 -->
              <span v-if="item.deadlineMode==='day' && !item.currentUserCoupon" class="text-10">领取后{{item.fixedDay}}天内有效</span>
              <!-- 有效期模式且用户已经领取 -->
              <span v-if="item.deadlineMode==='day' && item.currentUserCoupon" class="text-10">{{ timeExpire(item.createdTime, item.currentUserCoupon.deadline) }}</span>
          </div>
          <span class="coupon-button" @click="couponHandle(item, isReceive)">去使用</span>
        </div>
        <div class="e-coupon__middle"></div>
        <div class="e-coupon__bottom text-overflow">
          可用范围：{{ scopeFilter(item) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import Api from '@/api';
  import { Toast } from 'vant';
  import couponMixin from '@/mixins/coupon'
  import getCouponMixin from '@/mixins/coupon/getCouponHandler';

  const ALL_TYPE = {
    classroom: 'classroom',
    course: 'course',
    vip: 'vip',
    all: 'all'
  };
  export default {
    name:"pass-receive",
    mixins: [couponMixin, getCouponMixin],
    data() {
      return {
        item: {},
        message: '',
        isReceive: true
      };
    },
    computed: {
      couponStatus() {
        return Object.keys(this.item).length ? 'coupon-receive-success' : '';
      }
    },
    created() {
      // 通过链接领取优惠券
      const token = this.$route.params.token;

      // 未登录跳转登录页面
      if (!this.$store.state.token) {
        this.$router.push({
          name: 'login',
          query: {
            redirect: this.$route.fullPath,
          }
        });
        return;
      }

      if (token) {
        Api.receiveCoupon({
          data: { token }
        }).then(res => {
          this.item = res;
          this.message = '恭喜您成功领取了一张优惠券！';
        }).catch(err => {
          this.message = '优惠券领取失败！'
          Toast.fail(err.message);
        });
      }
    }
  }
</script>
