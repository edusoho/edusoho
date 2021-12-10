<template>
  <div class="coupon-item">
    <div class="coupon-item__top clearfix">
      <div class="coupon-item__value pull-left">
        {{ faceValue }}
      </div>
      <div class="coupon-item__info pull-left" v-if="!isMore">
        <p>{{ coupon.name }}</p>
        <p class="time">{{ validPeriod  }}</p>
      </div>
      <div class="coupon-item__btn pull-left">
        <a-button size="small">
          领卷
        </a-button>
      </div>
    </div>
    <div class="coupon-item__middle" />
    <div class="coupon-item__bottom">
      可用范围：{{ availableRange }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'CouponItem',

  props: {
    coupon: {
      type: Object,
      required: true
    },

    isMore: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    faceValue() {
      let text = '折';
      const { type, rate } = this.coupon;

      if (type === 'minus') {
        text = '元';
      }

      return `${rate} ${text}`;
    },

    validPeriod() {
      const { createdTime, deadlineMode, deadline, fixedDay } = this.coupon;

      let endTime = '';

      if (deadlineMode === 'day') {
        endTime = moment().add(fixedDay, 'days').format("YYYY-MM-DD");
      }

      if (deadlineMode === 'time') {
        endTime = moment(deadline).format('YYYY-MM-DD');
      }

      return `${moment(createdTime).format('YYYY-MM-DD')} - ${endTime}`;
    },

    availableRange() {
      const { numType, product } = this.coupon.targetDetail;

      let targetType = '全部商品';

      if (numType === 'single') {
        switch (product) {
          case 'course':
          case 'classroom':
            targetType = '指定商品';
            break;
          case 'vip':
            targetType = '指定会员';
            break;
          default:
            targetType = '';
        }
      } else if (numType === 'all') {
        switch (product) {
          case 'course':
            targetType = '全部课程';
            break;
          case 'classroom':
            targetType = '全部班级';
            break;
          case 'all':
            targetType = '全部商品';
            break;
          case 'vip':
            targetType = '全部会员';
            break;
          default:
            targetType = '';
        }
      } else {
        switch (product) {
          case 'course':
          case 'classroom':
            targetType = '部分商品';
            break;
          default:
            targetType = '';
        }
      }

      return targetType;
    }
  }
}
</script>

<style lang="less" scoped>
.coupon-item {
  position: relative;
  width: 100%;
  height: 100%;
  color: #fff;

  &::before,
  &::after {
    content: "";
    position: absolute;
    top: 70px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #fff;
  }

  &::before {
    left: -6px;
  }

  &::after {
    right: -6px;
  }

  &__top {
    padding-left: 16px;
    height: 80px;
    background-color: #ff6969;
    border-top-right-radius: 2px;
    border-top-left-radius: 2px;
  }

  &__value {
    font-size: 18px;
    line-height: 78px;
  }

  &__info {
    margin-top: 20px;
    margin-left: 8px;
    font-size: 14px;

    .time {
      margin-top: 8px;
      font-size: 12px;
    }

    p {
      margin: 0;
    }
  }

  &__btn {
    margin-top: 30px;
    margin-left: 16px;

    /deep/ .ant-btn {
      color: #ff6969;
    }
  }

  &__middle {
    margin-top: -6px;
    margin-bottom: -1px;
    height: 6px;
    background-image: url('/static-dist/app/img/vue/a.png');
    background-size: 16px 6px;
    background-position-x: 6px;
  }

  &__bottom {
    padding-left: 16px;
    height: 32px;
    line-height: 32px;
    font-size: 10px;
    background-color: #ff5353;
    border-bottom-right-radius: 2px;
    border-bottom-left-radius: 2px;
  }
}
</style>
