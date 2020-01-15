<template>
  <div class="app live-timetable">
    <e-navbar title="今日直播" />
    <div ref="liveList" class="live-list">
      <div class="live-list__title">
        <div class="live-title__left">直播课表</div>
        <div class="live-title__right">{{today}}</div>
      </div>
      <e-card
        v-if="isRequestComplete"
        v-for="(item,index) in liveCourse"
        :key="index"
        :course="item"
      />
      <empty v-if="noData" text="空空如也，暂无内容" class="empty__live" />
    </div>
  </div>
</template>

<script>
import ENavbar from "&/components/e-navbar/e-navbar.vue";
import ECard from "&/components/e-course-card/e-course-card";
import empty from "&/components/e-empty/e-empty.vue";
import { formatFullTime } from "@/utils/date-toolkit";
import Api from "@/api";
export default {
  name: "more-live",
  components: {
    ENavbar,
    ECard,
    empty
  },
  data() {
    return {
      today: "",
      liveCourse: [],
      isRequestComplete: false
    };
  },
  computed: {
    noData: function() {
      return this.isRequestComplete && this.liveCourse.length === 0;
    }
  },
  created() {
    this.today = formatFullTime(new Date());
    this.getmyLiveCourse(new Date());
  },
  methods: {
    getmyLiveCourse(time) {
      const params = this.getDayTime(time);
      Api.myliveCourse({ params }).then(res => {
        this.liveCourse = res;
        this.isRequestComplete = true;
      });
    },
    getDayTime(time) {
      const nowTimeDate = new Date(time);
      return {
        startTime: parseInt(nowTimeDate.setHours(0, 0, 0, 0) / 1000),
        endTime: parseInt(nowTimeDate.setHours(23, 59, 59, 999) / 1000)
      };
    }
  }
};
</script>

<style>
</style>