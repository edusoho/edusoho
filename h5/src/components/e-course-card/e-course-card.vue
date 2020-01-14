<template>
  <div class="e-course-card">
    <div class="live-list__item">
      <div class="live-item__top">
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
            <span class="live-content__time">{{course.startTime | formateTime}}</span>
            <template v-if=" course.type==='live' ">
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
          <template v-if=" course.type==='live' && status!=='end'">
            <div
              class="live-btn live-btn--default"
              :class="['live-btn', status==='default' ? 'live-btn--start' : 'live-btn--default']"
            >{{status | liveStatusText('btn')}}</div>
          </template>
        </div>
      </div>
      <div class="live-item__bottom" v-if="course.classroom">
        <span>{{course.classroom.title}}</span>
        <i class="iconfont icon-arrow-right"></i>
      </div>
    </div>
  </div>
</template>

<script>
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
    }
  }
};
</script>

<style>
</style>