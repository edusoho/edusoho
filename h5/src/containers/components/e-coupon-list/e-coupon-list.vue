<template>
  <div class="e-coupon">
    <div class="e-coupon__title">优惠券</div>
    <div :class="['e-coupon__container', 'clearfix', couponNum]" v-show="coupons.length">
      <van-swipe :width="196" :show-indicators="false" :loop="true" :touchable="true">
        <van-swipe-item v-for="(item, index) in coupons"
          :key="index" :class="['e-coupon__body', Number(item.unreceivedNum) == 0 ? 'coupon-received-all' : '']">
            <div class="e-coupon__header clearfix">
              <span class="e-coupon__price" v-html="priceHtml(item)"></span>
              <div class="e-coupon__name" v-show="coupons.length == 1">
                <div class="text-overflow text-14 coupon-name">{{ item.name }}</div>
                <span class="text-10">{{ timeExpire(item) }}</span>
              </div>
                <div class="stamp" v-if="item.currentUserCoupon"></div>
                <a href="javascript:0;" class="coupon-button" @click="handleClick(item)">{{ item.currentUserCoupon ? '去使用' : '领券' }}</a>
            </div>
            <div class="e-coupon__middle"></div>
            <div class="e-coupon__bottom text-overflow">
              可用范围：{{ scopeFilter(item) }}
            </div>
       </van-swipe-item>
      </van-swipe>
    </div>
  </div>
</template>

<script>
  // import Api from '@/api';
  import { Toast } from 'vant';

  export default {
    props: {
      coupons: {
        type: Array,
        default: []
      },
    },
    computed: {
      couponNum() {
        return this.coupons.length > 1 ? 'e-coupon-multi' : 'e-coupon-single';
      }
    },
    methods: {
      scopeFilter(item) {
        if (item.targetType === 'classroom') {
          if (item.target) {
            return '指定班级';
          }
          return '全部班级';
        } else if (item.targetType === 'course') {
          if (item.target) {
            return '指定课程';
          }
          return '全部课程';
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
        // const token = data.token;
        // Api.receiveCoupon({
        //   query: { token, }
        // }).then(res => {

        // }).catch(err => {
        //   Toast.fail('您好像没有登录哦');
        // });
        if (data.currentUserCoupon) {
          const couponType = data.targetType;
          if (data.target) {
            const id = data.target.id;
            this.$router.push({
              path: `${couponType}/${id}`
            })
          }
          return;
        }
      }
    }
  }
</script>
