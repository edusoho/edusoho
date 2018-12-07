<template>
  <div class="e-coupon">
    <div class="e-coupon__title" v-if="showTitle === 'show'">优惠券</div>
    <div :class="['e-coupon__container', 'clearfix', couponNum]" v-show="coupons.length">
      <van-swipe :width="196" :show-indicators="false" :loop="true" :touchable="true">
        <van-swipe-item v-for="(item, index) in coupons"
          :key="index">
          <item
            :item="item"
            :index="index"
            :num="coupons.length"
            @buttonClick="handleClick">
          </item>
       </van-swipe-item>
      </van-swipe>
    </div>
  </div>
</template>

<script>
  import { Toast } from 'vant';
  import item from './item.vue';

  export default {
    components: {
      item
    },
    props: {
      coupons: {
        type: Array,
        default: []
      },
      feedback: {
        type: Boolean,
        default: true
      },
      couponIndex: {
        type: Number,
        default: 0
      },
      showTitle: {
        type: String,
        default: 'show'
      }
    },
    computed: {
      couponNum() {
        return this.coupons.length > 1 ? 'e-coupon-multi' : 'e-coupon-single';
      }
    },
    methods: {
      scopeFilter(item) {
        const { targetType, target } = item;

        if (targetType === 'classroom') {
          return target ? target.title : '全部班级';
        }
        if (targetType === 'course') {
          return target ? target.title : '全部课程';
        }
        if (targetType === 'vip') {
          return '会员';
        }
        return '全部商品';
      },
      timeExpire(item) {
        const createdTime = item.createdTime.slice(0, 10);
        const deadline = item.deadline.slice(0, 10);
        return `${createdTime}至${deadline}`;
      },
      priceHtml(item) {
        const intPrice = parseInt(item.rate);
        let pointPrice = `.${Number(item.rate).toFixed(2).split('.')[1]}`;
        pointPrice = `${pointPrice == 0 ? '' : pointPrice}`;
        const typeText = item.type === 'discount' ? '折' : '元';
        return `${intPrice}<span class="text-14">${pointPrice + typeText}</span>`;
      },
      handleClick(data) {
        if (!this.feedback) return;
        data.couponIndex = this.couponIndex;
        this.$emit('couponHandle', data)
      }
    }
  }
</script>
