<template>
  <div class="e-coupon">
    <div class="e-coupon__title" v-if="showTitle === 'show'">优惠券</div>
    <div :class="['e-coupon__container', 'clearfix', couponNum]" v-show="coupons.length">
      <!-- 多张优惠券 -->
      <van-swipe v-if="coupons.length > 1" :width="200" :show-indicators="false" :loop="false" :touchable="true">
        <van-swipe-item v-for="(item, index) in coupons" :key="index">
          <item :item="item" :num="coupons.length" :feedback="feedback"
            @buttonClick="handleClick($event)">
          </item>
       </van-swipe-item>
      </van-swipe>
      <!-- 单张优惠券 -->
      <item v-else v-for="(item, index) in coupons" :key="index"
        :item="item" :num="coupons.length" :feedback="feedback"
        @buttonClick="handleClick($event)">
      </item>
    </div>
  </div>
</template>

<script>
  import item from './item.vue';

  export default {
    components: {
      item
    },
    props: {
      coupons: {
        type: Array,
        default: () => {
          return [];
        }
      },
      feedback: {
        type: Boolean,
        default: true
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
      handleClick(coupon) {
        if (!this.feedback) return;
        this.$emit('couponHandle', coupon)
      }
    }
  }
</script>
