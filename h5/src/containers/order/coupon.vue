<template>
  <div class="coupon-container">
    <!-- 优惠券列表 -->
    <div :class="[{ active: active === index }, 'coupon-item', type]" @click="onChange(index)">
      <div class="coupon-border-box">
        <div class="rate-number">{{ rate }}<span class="text-14">{{ type=='discount' ? '折' : '元' }}</span></div>
        <div class="coupon-info">
          <div class="title text-overflow">优惠券</div>
          <div class="grey-medium">有效期截止：{{couponEndDate}}</div>
          <span class="grey-medium">可用范围：{{couponType}}</span>
        </div>
        <i class="h5-icon h5-icon-checked-circle coupon-checked"></i>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: ['data', 'index', 'active'],
    computed: {
      couponEndDate() {
        return this.data.deadline.slice(0, 10);
      },
      couponType() {
        const couponTextPart = this.data.targetId ? '指定' : '全部';
        switch (this.data.targetType) {
          case 'course':
            return couponTextPart + '课程'
            break;
          case 'classroom':
            return couponTextPart + '班级'
            break;
          case 'vip':
            return couponTextPart + '会员课程'
            break;
          default:
            return '全部'
            break;
        }
      },
    },
    data() {
      return {
        activeIndex: this.active,
        discount: this.data.type == 'discount' ? true : false,
        type: this.data.type,
        rate: this.data.rate
      }
    },

    methods: {
      onChange(index) {
        this.$emit('chooseItem',
        {
          index: index,
          itemData: this.data
        })
      },
    }
  }
</script>