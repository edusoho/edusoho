<template>
  <div :class="['e-coupon__body', couponStatus]">
    <div class="e-coupon__header clearfix">
      <span class="e-coupon__price" v-html="priceHtml(item)"></span>
      <div class="e-coupon__name" v-show="num == 1">
        <div class="text-overflow text-14 coupon-name">{{ item.name }}</div>
        <span class="text-10">{{ timeExpire(item) }}</span>
      </div>
      <div class="stamp" v-if="item.currentUserCoupon"></div>
      <span class="coupon-button" @click="handleClick(item, index)">{{ item.currentUserCoupon ? '去使用' : '领券' }}</span>
    </div>
    <div class="e-coupon__middle"></div>
    <div class="e-coupon__bottom text-overflow">
      可用范围：{{ scopeFilter(item) }}
    </div>
  </div>
</template>

<script>
  export default {
    props: ['item', 'num', 'index'],
    computed: {
      couponStatus() {
        const currentUserCoupon = this.item.currentUserCoupon;
        if (this.item.unreceivedNum == 0 && !currentUserCoupon) {
          return 'coupon-received-all';
        }
        if (currentUserCoupon && currentUserCoupon.status === 'used') {
          return 'coupon-used'
        }
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
        return `${createdTime} 至 ${deadline}`;
      },
      priceHtml(item) {
        const intPrice = parseInt(item.rate);
        let pointPrice = `.${Number(item.rate).toFixed(2).split('.')[1]}`;
        pointPrice = `${pointPrice == 0 ? '' : pointPrice}`;
        const typeText = item.type === 'discount' ? '折' : '元';
        return `${intPrice}<span class="text-14">${pointPrice + typeText}</span>`;
      },
      handleClick(data, index) {
        this.$emit('buttonClick', {
          item: data,
          itemIndex: index,
        })
      }
    }
  }
</script>
