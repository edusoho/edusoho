<template>
  <!-- 优惠秒杀 -->
  <div class="detail-discount clearfix">
    <div class="pull-left detail-discount__left">
      <span class="text">限时优惠</span>
      <span class="price">129元</span>
      <s class="original-price">399元</s>
    </div>
    <div class="pull-left detail-discount__right">
      <p class="text">距离结束还剩</p>
      <div class="count-down">
        <van-count-down use-slot :time="time" @finish="onFinish" @change="onChange">
          <span class="day">{{ timeData.days }}</span>天
          <span class="item">{{ timeData.hours | checkTime }}</span>:
          <span class="item">{{ timeData.minutes | checkTime }}</span>:
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
      timeData: {days: 0, hours: 0, minutes: 0, seconds: 0}
    }
  },
  methods: {
    onChange(e) {
      this.timeData = e;
    },
    // 倒计时结束
    onFinish() {
      console.log('倒计时结束了');
    }
  },
  filters: {
    checkTime(i) {
      if (i < 10 && i >= 0) {
        i = `0${i}`;
      }
      return i;
    }
  }
}
</script>