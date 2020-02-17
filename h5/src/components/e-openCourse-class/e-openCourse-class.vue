<template>
  <div class="e-openCourse-class">
    <div class="column-class-left">
      <img v-lazy="course.middlePicture">
      <div class="column-class-left__live">
        <div>
          <span v-show="course.type === 'liveOpen'">直播</span>
        </div>
        <div v-if="course.studentNum">
          <i class="iconfont icon-renqi"/>
          {{ course.studentNum }}
        </div>
      </div>
    </div>
    <div class="column-class-right">
      <div class="column-class-right__top text-overflow">
        {{ course.title }}
      </div>
      <div class="column-class-right__live  text-overflow" >
        <span v-show="status!=='default'">{{formatTime(course.lesson.startTime)}} </span>
        <span class="live-time">{{ formatHour(course.lesson.startTime)}}</span>
        <span :class="getStatusClass(status)"> {{  status | liveStatusText}}</span>
          <i
                v-show=" status==='default' "
                class="iconfont icon-zhibo1"
              ></i>
       </div>
    </div>
  </div>
</template>

<script>
import { formatSimpleHour,formatDotTime } from '@/utils/date-toolkit';
export default {
  props: {
    course: {
      type: Object,
      default: () => {}
    },
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
    }
  },
  methods:{
    formatHour(time){
        return formatSimpleHour(new Date(time*1000));
    },
    formatTime(time){
         return formatDotTime(new Date(time*1000));
    },
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
  }
}
</script>
