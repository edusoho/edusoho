<template>
  <!-- 倒计时 -->
  <div :class="className" v-if="isShow">
    <img class="clock-icon" :src="clockIcon" />
    <span v-if="time">{{ time }}</span>
    <span v-else>00:00:00</span>
  </div>
</template>

<script>
import Emitter from "common/vue/mixins/emitter";
import Locale from "common/vue/mixins/locale";
import { getCountDown } from "common/date-toolkit";

const clockIcon = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGgAAABoCAMAAAAqwkWTAAAAP1BMVEX///9mZmafn5/s7OxwcHB5eXmMjIz19fXGxsaysrK8vLyDg4PZ2dnj4+OoqKizs7OpqamWlpbPz8/Y2Nji4uLjiUH7AAAE6klEQVRo3u1Z2baiMBCULJ2FEBb9/2+dKyKVINkcnTMPt54EIUV3qjud9OUX/wjqatgPzKIu3wT3onuC8cvXYEUXQNjLl2C6A8zlK7DdC75iE9/81rMf9Jv3vjFPbB1auo1Wrpf+8nGoB4/ar1cm8XmVLytR4Cq33lgN/Lzk+vBOD+F9fIrY651fov+NiDtt7+nbG6PpW0SnmNVLwJmZDH87XK3sEiAdjTqINbCHt2iu1GUxLyHPCvEGk+67IqRG5t2YllYaeRiS2B0zyRMqHt7ULTQDdUDvD9Ox+D6k4huPEK2rlZowDNnTRM014RmxTc8g2lZg3u8jjCrz2BR70cGFdTZpAZqGidRYF+uWK3NKwxdt/D0zWHcLnx53qUcrsKvPBMQRTobieossyPjzBRYulLqWR1hkFnkaQJ4fqz4WBK6r5JG8lBtQskICbA9cWclDahsBNFkqNT+vJb6zQgfseSk6QMiZ/YB6cVazTnFA1ZWkI6Jpg2Bhahg0k0FaiNRX5kFinBBND9DJzC4MJX/g9TohyDAeTGmXwtnBfVSbUw1KUvAQz3iAIiaFD82Ch9W7qdsJWSRRuH6ocpwFT1mliCATiIkK6fEpbKivV/WJXgfTpCsM4nABeLJQDyYRvCnLBo3gBE8dUw+fZ02S+BSD+aljkpgmJUB6CuR2qK8E7j3f1IY3TCFkZxjEKtfiQTwGh9pgEuVjaMJPquHBVpYedsAklZUCh0G8hgdEPDAp5RB8EeEVVseDmWSwgzIeUZDCVGXQcFx3OIRnM767YnQJg2p4dtCuJpXR3bSL/7Y+tTTyxGcCffrAg/a/bC6FXC3z1kU8gNh9N6UnSexmZHaNi9xS+gsP5EC7hEVaC8NOqVPuBWIeDA9h8KQWMEVDM088fFINep+Ya8pqneeJ/S9TbjG7e20q98qt6pplshDF8ITccEI0gzLlXKZ+5pOlHDPvsmPrr5T/WfwLgOoV0rNNVNM+ebKCP3KPTJvN6Wdw/30i3IeL/PtEZdcJhYW77DqfEgOLZRHDVYiBdgnMKTFYZI+sdjs5s/Q+q18tBWU2YJdUwC5dBJ1KmA4RlU5ByCOlFDQd/0X2yqYgDF9OquBJJtVbeokVcfZg2bMLuaR3vwT/FxY+g4de4axn1uVKXYuFL7OUv31KD7UMhaXcwasSn9MEtiuXp741LvoMhNECjnJS50pVOhSa7L2jZJ4vIOOij5pNQn2LnzrbIzKYr6ZZwteVS11Ceqb2Jp4N/C3zRwAOJmFbVQsuYIUulbo9TMLBRiWPPO5/ZXGzbEDaulmWFZtlfIr42+0/OAsmUVgu9ryOB+JhZYNitY2tRzQj1Fc85hzCzeLYdug0xuorYArVNtYeo4FHQX1ldyMpjJUHgxh6huPqAo/BLRvVkmthCQ2HVAU61GYw0Rsk07ew0bJWXbE0TX33A/6acAmIntZGlRThzTE+MB8bW5SzwjykAako6lrXMbQMylTkXloGQHsThPvzJsioTpogzUyxrAd7bOuMLoymVh5IAOoDmbOPRpVeeNROFoimVliBzmcKoEE0tYPLoB1VpkE0tWNMtcTBYgnPTKqdAkYBvV9u0Z86avhSvknQ3sKe2R0nLWzgbaoietD8DZY5yYDc0I5CSxxAE/2jUM6TiDkEeQeWD7Npc88MzFjt+OW/xB9y8yiXTVs2xQAAAABJRU5ErkJggg==`;

export default {
  name: "count-down",
  mixins: [Emitter, Locale],
  data() {
    return {
      time: null,
      clockIcon,
      timeMeter: null //计时器
    };
  },
  props: {
    getCurrentTime: {},
    limitedTime: {
      type: Number,
      default: 0
    },
    assessmentStatus: {
      type: String,
      default: ""
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
    this.$nextTick(() => {
      if (this.assessmentStatus === "preview" || this.mode !== "do") {
        return;
      }
      this.timer();
    });
  },
  beforeDestroy() {
    this.clearTime();
  },
  computed: {
    className: function() {
      return this.limitedTime &&
        this.assessmentStatus !== "preview" &&
        this.mode === "do"
        ? "ibs-assessment-timer ibs-assessment-timer--top"
        : "ibs-assessment-timer";
    },
    isShow: function() {
      return (this.beginTime + this.limitedTime * 60) * 1000 - Date.now() > 0;
    }
  },
  methods: {
    getContainer() {
      return document.getElementById("item-bank-sdk-card");
    },
    reachTimerSubmit() {
      this.$emit("reachTimeSubmitAnswerData");
    },
    timer() {
      const time = (this.beginTime + this.limitedTime * 60) * 1000 - Date.now();

      if (time <= 0) {
        this.arrivalTime();
        return;
      }

      // 考试倒计时
      let i = 0;
      this.timeMeter = setInterval(() => {
        let { hours, minutes, seconds } = getCountDown(time, i++);
        this.time = `${hours}:${minutes}:${seconds}`;
        if (
          (Number(hours) == 0 &&
            Number(minutes) == 0 &&
            Number(seconds) == 0) ||
          Number(seconds) < 0
        ) {
          this.arrivalTime();
        }
      }, 1000);
    },
    arrivalTime() {
      this.clearTime();
      //直接交卷
      this.reachTimerSubmit();
    },
    clearTime() {
      clearInterval(this.timeMeter);
      this.timeMeter = null;
    }
  }
};
</script>
