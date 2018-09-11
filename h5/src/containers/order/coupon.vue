<template>
  <div class="coupon-container">
    <!-- 优惠券列表 -->
    <div :class="[{ active: active === index }, 'coupon-item', type]" @click="onChange(index)">
      <div class="coupon-border-box">
        <div class="rate-number">666<span class="text-14">元</span></div>
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
      couponData: {
        get() {
          return this.data;
        },
        set() {}
      },
      couponEndDate: {
        get() {
          return this.couponData.deadline.slice(0, 10);
        },
        set() {}
      },
      couponType: {
        get() {
          switch (this.couponData.targetType) {
            case 'course':
              return '指定课程'
              break;
            case 'classroom':
              return '指定班级'
              break;
            case 'vip':
              return '会员课程'
              break;
            default:
              return '全部课程'
              break;
          }
        },
        set() {}
      },
    },
    data() {
      return {
        activeIndex: this.active,
        discount: this.data.type == 'discount' ? true : false,
        chosenCoupon: -1,
        type: this.data.type
      }
    },

    methods: {
      onChange(index) {
        console.log(222,this.data);
        this.$emit('chooseItem',
        {
          index: index,
          itemData: this.couponData
        })
        this.chosenCoupon = index;
        this.active = true;
      },
    }
  }
</script>