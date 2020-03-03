<template>
  <div class="e-course-card">
    <div class="live-list__item">
      <div class="live-item__top" @click="toCourse()">
        <span>{{course.course.displayedTitle}}</span>
        <i class="iconfont icon-arrow-right"></i>
      </div>
      <div class="live-item__content">
        <div class="live-content__left">
          <div class="live-content__title">
            <i class="iconfont icon-zhibo"></i>
            {{course.activity.title}}
          </div>
          <div class="live-content__dec">
            <span class="live-content__time">{{course.startTime | formateLiveTime}}</span>
            <span :class="getStatusClass(status)">
              {{status | liveStatusText }}
              <i
                v-show=" status==='default' "
                class="iconfont icon-zhibo1"
              ></i>
            </span>
          </div>
        </div>
        <div class="live-content__right" v-if="status!=='end'">
          <div
            :class="['live-btn', status==='default' ? 'live-btn--start' : 'live-btn--default']"
            @click="toTask()"
          >{{status | liveBtnText }}</div>
        </div>
      </div>
      <div class="live-item__bottom" v-if="course.classroom" @click="toClassroom()">
        <span>{{course.classroom.title}}</span>
        <i class="iconfont icon-arrow-right"></i>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "e-live-card",
  props: {
    course: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    status() {
      if (this.course.type === "live") {
        const now = new Date().getTime();
        const startTimeStamp = new Date(this.course.startTime);
        const endTimeStamp = new Date(this.course.endTime);
        // 直播未开始
        if (now <= startTimeStamp) {
          return `nostart`;
        }
        if (now > endTimeStamp) {
          if (this.course.activity.replayStatus === "ungenerated") {
            return "end";
          }
          return "replay";
        }
        return "default";
      }
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
    toClassroom() {
      this.$emit("toClassroom",this.course.classroom.id)
    },
    toTask(){
      const task={
        id:this.course.id,
        type:this.course.type,
        courseId:this.course.course.id
      }
      this.$emit("toTask",task)
    },
    toCourse(){
       this.$emit("toCourse",this.course.course.id)
    }
  }
};
</script>

<style>
</style>