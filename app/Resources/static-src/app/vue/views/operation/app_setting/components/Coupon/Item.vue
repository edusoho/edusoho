<template>
  <div class="coupon-item clearfix" :class="{ 'single': single }">
    <img v-if="single" class="coupon-item__bg" src="/static-dist/app/img/vue/decorate/single-coupon_bg.png">
    <img v-else class="coupon-item__bg" src="/static-dist/app/img/vue/decorate/coupon_bg.png" alt="">
    
    <div class="clearfix" style="position: absolute; top: 0; right: 0; bottom: 0; left: 0;">
      <div class="pull-left y-center ml16">
        <div class="coupon-item__value">{{ faceValue }}</div>
        <div class="coupon-item__range">{{ 'available_range' | trans }}：{{ availableRange }}</div>
      </div>
      
      <div class="coupon-item__info y-center pull-left" v-if="!isMore">
        <p>{{ coupon.name }}</p>
        <p class="time">{{ validPeriod  }}</p>
      </div>

      <div class="coupon-item__btn y-center pull-right">
        {{ 'collar_roll' | trans }}
      </div>
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
    },

    single: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    faceValue() {
      let text = Translator.trans('fold');
      const { type, rate } = this.coupon;

      if (type === 'minus') {
        text = Translator.trans('cny');
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

      let targetType = Translator.trans('all_products');

      if (numType === 'single') {
        switch (product) {
          case 'course':
          case 'classroom':
            targetType = Translator.trans('designated_goods');
            break;
          case 'vip':
            targetType = Translator.trans('designated_member');
            break;
          default:
            targetType = '';
        }
      } else if (numType === 'all') {
        switch (product) {
          case 'course':
            targetType = Translator.trans('all_courses');
            break;
          case 'classroom':
            targetType = Translator.trans('all_classes');
            break;
          case 'all':
            targetType = Translator.trans('all_products');
            break;
          case 'vip':
            targetType = Translator.trans('all_members');
            break;
          default:
            targetType = '';
        }
      } else {
        switch (product) {
          case 'course':
          case 'classroom':
            targetType = Translator.trans('some_products');
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

.y-center {
  position: relative;
  top: 50%;
  transform: translateY(-50%);
}

.coupon-item {
  position: relative;
  display: inline-block;
  width: 224px;
  height: 80px;
  color: #fff;

  &.single {
    width: 343px;
  }

  &__value {
    font-size: 24px;
    line-height: 24px;
    color: #fff;
    font-weight: 600;
  }

  &__range {
    margin-top: 2px;
    margin-left: -10px;
    font-size: 12px;
    line-height: 20px;
    color: #fff;
    font-weight: 400;
    transform: scale(0.83);
  }

  &__info {
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
    width: 48px;
    height: 22px;
    margin-right: 20px;
    text-align: center;
    line-height: 22px;
    font-size: 12px;
    color: #fff;
    font-weight: 500;
    background: #FF900E;
    border-radius: 28px;
  }
}
</style>
