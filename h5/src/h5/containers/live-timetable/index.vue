<template>
  <div class="live-timetable app">
    <e-navbar title="直播课表" />
    <Calendar
      ref="Calendar"
      :markDateMore="liveTime"
      :markDate="arr2"
      v-on:isToday="clickToday"
      v-on:choseDay="clickDay"
      v-on:changeMonth="changeDate"
    ></Calendar>
    <div class="bg-gary"></div>
    <div ref="liveList" class="live-list" :style="{ height: liveHeight + 'px' }">
      <div class="live-list__title">
        <div class="live-title__left">直播课表</div>
        <div class="live-title__right">{{today}}</div>
      </div>
      <e-card v-if="isRequestComplete" v-for="(item,index) in liveCourse" :key="index" :course="item" />
      <empty v-if="noData" text="空空如也，暂无内容" class="empty__live" />
    </div>
  </div>
</template>

<script>
import ENavbar from "&/components/e-navbar/e-navbar.vue";
import ECard from "&/components/e-card/e-live-card";
import Calendar from "vue-calendar-component";
import empty from "&/components/e-empty/e-empty.vue";
import { formatFullTime, compareDate } from "@/utils/date-toolkit";
import Api from "@/api";
import { parse } from "querystring";
export default {
  name: "live-timetable",
  components: {
    ENavbar,
    Calendar,
    ECard,
    empty
  },
  data() {
    return {
      arr2: [],
      liveTime: [],
      liveHeight: null,
      today: "",
      liveCourse: [],
      isRequestComplete: false
    };
  },
  created() {},
  computed: {
    noData: function() {
      return this.isRequestComplete && this.liveCourse.length === 0;
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.liveHeight =
        document.body.clientHeight - this.$refs.liveList.offsetTop;
      this.showToday();
      this.getmyLiveCourse(new Date());
    });
  },
  methods: {
    //获取时间课表，按照一个月的时间段获取
    getliveSchedule(time) {
      const params = this.getMonthTime(time);
      let className = "";
      let liveTime = [];
      let nowTime = new Date();
      Api.liveSchedule({ params }).then(res => {
        res.forEach(item => {
          className = compareDate(new Date(item.date * 1000), nowTime)
            ? "mark1"
            : "mark2";
          liveTime.push({
            date: new Date(item.date * 1000).toLocaleDateString(),
            className
          });
        });
        this.liveTime = liveTime;
      });
    },
    getmyLiveCourse(time) {
      const params = this.getDayTime(time);
      Api.myliveCourse({ params }).then(res => {
        this.liveCourse = res;
        this.isRequestComplete = true;
      });
    },
    clickDay(data) {
      console.log("选中了", data); //选中某天
      this.today = formatFullTime(new Date(data));
      this.getmyLiveCourse(new Date(data))
      //this.$toast("选中了" + data);
    },
    clickToday(data) {
      console.log("跳到了本月今天", data); //跳到了本月
    },
    changeDate(data) {
      // this.$toast("切换到的月份为" + data);
      this.getliveSchedule(data);
      console.log("左右点击切换月份", data); //左右点击切换月份
    },
    showToday() {
      this.today = formatFullTime(new Date());
      this.$refs.Calendar.ChoseMonth(this.today); //跳到当天日期
    },
    getDayTime(time) {
      const nowTimeDate = new Date(time);
      return {
        startTime: parseInt(nowTimeDate.setHours(0, 0, 0, 0) / 1000),
        endTime: parseInt(nowTimeDate.setHours(23, 59, 59, 999) / 1000)
      };
    },
    getMonthTime(time) {
      // 获取时间戳 (本月第一天00.00.00  本月最后一天23.59.59)
      var data = new Date(time); //本月
      data.setDate(1);
      data.setHours(0);
      data.setSeconds(0);
      data.setMinutes(0);
      var data1 = new Date(time); // 下月
      if (data.getMonth() == 11) {
        data1.setMonth(0);
      } else {
        data1.setMonth(data.getMonth() + 1);
      }
      data1.setDate(1);
      data1.setHours(0);
      data1.setSeconds(0);
      data1.setMinutes(0);
      return {
        startTime: parseInt(data.getTime() / 1000),
        endTime: parseInt(data1.getTime() / 1000) - 1
      };
    }
  }
};
</script>

<style scoped>
.live-timetable .wh_container >>> .wh_content_all {
  background-color: #ffffff;
}
.live-timetable .wh_container >>> .wh_content_li {
  color: #303133 !important;
}
.live-timetable .wh_container >>> .wh_isToday {
  background: #ffffff !important;
  color: #303133 !important;
}
.live-timetable .wh_container >>> .wh_jiantou1 {
  width: 11px !important;
  height: 11px !important;
  border-top: 2px solid #bebebe !important;
  border-left: 2px solid #bebebe !important;
}
.live-timetable .wh_container >>> .wh_jiantou2 {
  width: 11px !important;
  height: 11px !important;
  border-top: 2px solid #bebebe !important;
  border-right: 2px solid #bebebe !important;
}
.live-timetable .wh_container >>> .wh_content_item {
  color: #333333 !important;
}
.live-timetable .wh_container >>> .wh_top_changge li {
  font-size: 16px !important;
}
.live-timetable .wh_container >>> .wh_top_tag {
  color: #999999 !important;
}
.live-timetable .wh_container >>> .wh_other_dayhide {
  color: rgba(3, 199, 119, 0.5) !important;
}
.live-timetable .wh_container >>> .wh_chose_day {
  background: #03c777 !important;
  border-radius: 50% !important;
  color: #ffffff !important;
}
.live-timetable .wh_container >>> .mark1,
.wh_container >>> .mark2 {
  background-color: #ffffff;
  position: relative;
}
.live-timetable .wh_container >>> .mark1::after {
  position: absolute;
  content: "";
  width: 5px;
  height: 5px;
  background: #03c777;
  bottom: 3px;
  left: 50%;
  margin-left: -2px;
  border-radius: 50%;
}
.live-timetable .wh_container >>> .mark2::after {
  position: absolute;
  content: "";
  width: 5px;
  height: 5px;
  background: orange;
  bottom: 3px;
  left: 50%;
  margin-left: -2px;
  border-radius: 50%;
}
</style>