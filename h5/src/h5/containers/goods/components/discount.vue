<template>
  <!-- 优惠秒杀 -->
  <div v-if="currentSku.displayPrice != 0" class="detail-discount clearfix">
    <div class="pull-left detail-discount__left">
      <span class="text">限时优惠</span>
      <span
        v-if="currentSku.displayPriceObj.currency === 'RMB'"
        class="price"
      >{{ currentSku.displayPriceObj.amount | formatPrice }}元
          </span>
      <span
        v-if="currentSku.displayPriceObj.currency === 'coin'"
        class="price"
      >{{ currentSku.displayPriceObj.coinAmount | formatPrice
        }}
      </span>
      <s class="original-price">
        <span
          v-if="currentSku.priceObj.currency === 'RMB'"
        >{{ currentSku.priceObj.amount | formatPrice }}元
          </span>
        <span
          v-if="currentSku.priceObj.currency === 'coin'"
        >{{ currentSku.priceObj.coinAmount | formatPrice
          }}
        </span>
      </s>
    </div>
    <div class="pull-left detail-discount__right">
      <p class="text">距离结束还剩</p>
      <div class="count-down">
        <van-count-down
          use-slot
          :time="time"
          @finish="onFinish"
          @change="onChange"
        >
          <span class="day">{{ timeData.days }}</span
          >天 <span class="item">{{ timeData.hours | checkTime }}</span
          >: <span class="item">{{ timeData.minutes | checkTime }}</span
          >:
          <span class="item">{{ timeData.seconds | checkTime }}</span>
        </van-count-down>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      time: 1432111,
      timeData: { days: 0, hours: 0, minutes: 0, seconds: 0 },
    };
  },
  props: {
    goods: {
      type: Object,
      default: () => {},
    },
    currentSku: {
      type: Object,
      default: () => {},
    },
  },
  methods: {
    onChange(e) {
      this.timeData = e;
    },
    // 倒计时结束
    onFinish() {
      console.log('倒计时结束了');
    },
  },
  filters: {
    formatPrice(input) {
      return (Math.round(input * 100) / 100).toFixed(2);
    },
    checkTime(i) {
      if (i < 10 && i >= 0) {
        i = `0${i}`;
      }
      return i;
    },
  },
  created() {
    const discount = this.goods.discount;
    this.time = (discount.endTime * 1000 - Date.now());
    console.log(discount.endTime);
    console.log(this.time);
  },
};
</script>
