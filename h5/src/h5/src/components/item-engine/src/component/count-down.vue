<template>
  <div :class="['ibs-count-down', timeWarn ? 'ibs-count-down-warn' : '']">
    <van-count-down
      v-if="isShow"
      :time="time"
      format="HH 时 mm 分 ss 秒"
      @change="change"
      @finish="finish"
    />
  </div>
</template>
<script>
export default {
  name: "ibs-count-down",
  data() {
    return {
      time: 0,
      timeWarn: false
    };
  },
  props: {
    limitedTime: {
      type: Number,
      default: 0
    },
    usedTime: {
      type: Number,
      default: 0
    },
    mode: {
      type: String,
      default: "do"
    },
    beginTime: {
      type: Number,
      default: 0
    }
  },
  mounted() {
    this.timer();
  },
  beforeDestroy() {},
  computed: {
    isShow: function() {
      const time =
        this.beginTime * 1000 +
          this.limitedTime * 60 * 1000 -
          Date.parse(new Date()) >
        0;
      if (this.limitedTime && this.mode === "do" && time) {
        return true;
      }
      return false;
    }
  },
  methods: {
    timer() {
      if (!this.limitedTime) {
        return;
      }
      const now = Date.parse(new Date());
      let time = this.beginTime * 1000 + this.limitedTime * 60 * 1000 - now;
      if (time <= 0) {
        this.reachTimerSubmit();
        return;
      }
      this.time = time;
    },
    reachTimerSubmit() {
      this.$emit("reachTimeSubmitAnswerData");
    },
    change(e) {
      const warnTime = 1;
      if (e.minutes < warnTime) {
        this.timeWarn = true;
      }
    },
    finish() {
      this.reachTimerSubmit();
    }
  }
};
</script>
