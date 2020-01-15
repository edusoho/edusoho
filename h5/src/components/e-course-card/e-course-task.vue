<template>
  <div class="e-course-card" v-if="course.target">
    <div class="live-list__item">
      <div class="live-item__top">
        <span>{{course.target.course.displayedTitle}}</span>
        <i class="iconfont icon-arrow-right"></i>
      </div>
      <div class="live-item__content">
        <div class="live-content__left">
          <div class="live-content__title">
            <i :class="['iconfont',iconfont(course.target.task)]"></i>{{course.target.task.title}}
          </div>
          <div class="live-content__dec" >
            <span class="live-content__time"> {{ course.target.task | formateTime}}</span>
            <template v-if=" course.target.task.type==='live' ">
              <span :class="getStatusClass(status)">
                {{status | liveStatusText('status')}}
                <i
                  v-show=" status==='default' "
                  class="iconfont icon-zhibo1"
                ></i>
              </span>
            </template>
          </div>
        </div>
        <div class="live-content__right">
          <template v-if=" course.target.task.type==='live' ">
            <div 
              v-if=" status!=='end' "
              :class="['live-btn', status==='default' ? 'live-btn--start' : 'live-btn--default']"
            >{{status | liveStatusText('btn')}}</div>
          </template>
           <template v-else>
            <div  
              :class="['live-btn', 'live-btn--start']"
            >继续学习</div>
          </template>
        </div>
      </div>
      <div class="live-item__bottom" v-if="course.classroom">
        <span>{{course.target.classroom.title}}</span>
        <i class="iconfont icon-arrow-right"></i>
      </div>
    </div>
  </div>
</template>

<script>
import { formatTimeByNumber } from "@/utils/date-toolkit";
export default {
  name: "e-course-card",
  props: {
    course: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    status() {
      if (this.course.target.task.type=== "live") {
        const now = new Date().getTime();
        const startTimeStamp = new Date(this.course.target.task.startTime*1000);
        const endTimeStamp = new Date(this.course.target.task.endTime*1000);
        // 直播未开始
        if (now <= startTimeStamp) {
          return `nostart`;
        }
        if (now > endTimeStamp) {
          // if (this.course.activity.replayStatus === "ungenerated") {
          //   return "end";
          // }
          return "replay";
        }
        return "default";
      }
    }
  },
  filters: {
    liveStatusText: function(value, type) {
      if (value === "replay") {
        return type === "btn" ? "立即回放" : "观看回放";
      }
      if (value === "default") {
        return type === "btn" ? "进入教室" : "正在播放";
      }
      if (value === "nostart") {
        return "即将开始";
      }
      if (value === "end") {
        return "已结束";
      }
    }
  },
  methods: {
    getStatusClass(value) {
      if (value === "replay") {
        return "live-status--end";
      }
      if (value === "default") {
        return "live-status--start";
      }
      if (value === "nostart") {
        return "live-status--default";
      }
    },
    // 任务图标(缺少下载)
    iconfont(task) {
      const type = task.type;
      switch (type) {
        case "audio":
          return "icon-yinpin";
          break;
        case "doc":
          return "icon-wendang";
          break;
        case "exercise":
          return "icon-lianxi";
          break;
        case "flash":
          return "icon-flash";
          break;
        case "homework":
          return "icon-zuoye";
          break;
        case "live":
          return "icon-zhibo";
          break;
        case "ppt":
          return "icon-ppt";
          break;
        case "discuss":
          return "icon-taolun";
          break;
        case "testpaper":
          return "icon-kaoshi";
          break;
        case "text":
          return "icon-tuwen";
          break;
        case "video":
          return "icon-shipin";
          break;
        case "download":
          return "icon-xiazai";
          break;
        default:
          return "";
      }
    }
  }
};
</script>

<style>
</style>