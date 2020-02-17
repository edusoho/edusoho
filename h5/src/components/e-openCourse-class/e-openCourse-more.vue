<template>
  <div class="e-openCourse-class">
    <div class="openCourse-class-left">
      <img v-lazy="course.middlePicture"/>
      <div class="openCourse-class-left__live">
        <div>
          <span >直播</span>
        </div>
        <div v-if="course.studentNum">
          <i class="iconfont icon-renqi" />
          {{ course.studentNum }}
        </div>
      </div>
    </div>

    <div class="openCourse-class-right">
      <div class="openCourse-class-right__top text-overflow">{{ course.title }}</div>
      <div class="openCourse-class-right__bottom">
        <div class="openCourse-class-right__live">
          <span :class="getStatusClass(status)" v-if="status === 'default'" >
            正在直播
            <i class="iconfont icon-zhibo1"></i>
          </span>
          <span :class="getStatusClass(status)" v-else>
            {{ course.lesson.startTime | filterOpenCourse }}
          </span>
          <div class="live-content__right" v-if="status !== 'end'">
            <div
              :class="[ 'live-btn', status === 'default' ? 'live-btn--start' : 'live-btn--default']"
              @click="toTask()"
            >
              {{ status | liveBtnText }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { formatChinaDay,formatSimpleHour } from '@/utils/date-toolkit';
export default {
  data(){
    return{
    }
  },
  props:{
    course:{
      type:Object,
      default:()=>{}
    }
  },
  computed: {
    status() {
        const now = new Date().getTime();
        const startTimeStamp = this.course.lesson.startTime*1000;
        const endTimeStamp = this.course.lesson.endTime*1000;
        // 直播未开始
        if (now <= startTimeStamp) {
          return `nostart`;
        }
        if (now > endTimeStamp) {
          if (this.course.lesson.replayStatus === "ungenerated") {
            return "end";
          }
          return "replay";
        }
        return "default";
    }
  },
  filters: {
    liveStatusText: function(value) {
      switch (value) {
        case "replay":
          return "观看回放";
        case "default":
          return "正在直播";
        case "nostart":
          return "即将开始";
        case "end":
          return "已结束";
        default:
          return "";
      }
    },
    liveBtnText: function(value) {
      switch (value) {
        case "replay":
          return "观看回放";
        case "default":
          return "进入教室";
        case "nostart":
          return "即将开始";
        case "end":
          return "已结束";
        default:
          return "";
      }
    },
    filterOpenCourse: function(value){
       const startTimeStamp = new Date(value * 1000)
       return `${formatChinaDay(startTimeStamp)} ${formatSimpleHour(startTimeStamp)}`
    }
  },
  methods: {
    getStatusClass(value) {
      switch (value) {
        case "replay":
          return "live-status--end";
        case "default":
          return "live-status--start";
        case "nostart":
          return "live-status--default";
        case "end":
          return "";
        default:
          return "";
      }
    },
    toTask(){
      const task={
        taskId:this.course.lesson.id,
        taskType:this.course.type,
        courseId:this.course.id,
      }
       window.postNativeMessage({
        action: "kuozhi_learn_task",
        data: task
      });
    },
  }
};
</script>
