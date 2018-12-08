<template>
  <div class="e-coupon">
    <div class="e-coupon__title" v-if="showTitle === 'show'">优惠券</div>
    <div :class="['e-coupon__container', 'clearfix', couponNum]" v-show="coupons.length">
      <van-swipe :width="196" :show-indicators="false" :loop="false" :touchable="true">
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
      handleClick(data) {
        if (!this.feedback) return;
        this.$emit('couponHandle', data)
      }
    }
  }
</script>
