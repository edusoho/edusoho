<template>
  <div class="app live-timetable">
    <div ref="liveList" class="live-list">
      <div class="live-list__title">
        <div class="live-title__left">直播课表</div>
        <div class="live-title__right">{{today}}</div>
      </div>
      <div class="text-center mt20">
          <van-loading size="24px" v-show="isLoad">加载中...</van-loading>
      </div>
      <e-card
        v-if="isRequestComplete"
        v-for="(item,index) in liveCourse"
        :key="index"
        :course="item"
        @toClassroom="toClassroom"
        @toTask="toTask"
        @toCourse="toCourse"
      />
      <empty v-if="noData" text="空空如也，暂无内容" class="empty__more-live" />
    </div>
  </div>
</template>

<script>
import ECard from "&/components/e-card/e-live-card";
import empty from "&/components/e-empty/e-empty.vue";
import { formatFullTime } from "@/utils/date-toolkit";
import * as types from "@/store/mutation-types";
import Api from "@/api";
import axios from "axios";
export default {
  name: "more-live",
  components: {
    ECard,
    empty
  },
  data() {
    return {
      today: "",
      liveCourse: [],
      isRequestComplete: false,
      token: "",
      isLoad:true
    };
  },
  computed: {
    noData: function() {
      return this.isRequestComplete && this.liveCourse.length === 0;
    }
  },
  created() {
    this.setTitle();
    this.getUserInfo();
    this.today = formatFullTime(new Date());
  },
  beforeRouteLeave() {
    this.$store.commit(types.USER_LOGOUT);
  },
  methods: {
    setTitle() {
      window.postNativeMessage({
        action: "kuozhi_native_header",
        data: { title: "今日直播" }
      });
    },
    getmyLiveCourse(time) {
      const params = this.getDayTime(time);
      Api.myliveCourse({
        params,
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Auth-Token": this.token
        }
      })
        .then(res => {
          this.liveCourse = res;
          this.isRequestComplete = true;
          this.isLoad=false;
        })
        .catch(error => {
          this.sendError(error);
        });
    },
    getDayTime(time) {
      const nowTimeDate = new Date(time);
      return {
        startTime: parseInt(nowTimeDate.setHours(0, 0, 0, 0) / 1000),
        endTime: parseInt(nowTimeDate.setHours(23, 59, 59, 999) / 1000)
      };
    },
    getUserInfo() {
      const self = this;
      window.nativeCallback = function(res) {
        self.token = res.token;
        self.getmyLiveCourse(new Date());
      };
      window.postNativeMessage({
        action: "kuozhi_login_user",
        data: { force: 1 }
      });
    },
    toClassroom(id) {
      window.postNativeMessage({
        action: "kuozhi_classroom",
        data: { classroomId: id }
      });
    },
    toTask(task) {
      const data = {
        taskId: task.id,
        taskType: task.type,
        courseId: task.courseId
      };
      window.postNativeMessage({
        action: "kuozhi_learn_task",
        data: { taskId: task.id, taskType: task.type, courseId: task.courseId }
      });
    },
    toCourse(id) {
      window.postNativeMessage({
        action: "kuozhi_course",
        data: { courseId: id }
      });
    },
    sendError(error) {
      window.postNativeMessage({
        action: "kuozhi_h5_error",
        data: {
          code: error.code,
          message: error.message
        }
      });
    }
  }
};
</script>

<style>
</style>