<template>
  <div class="e-course-card" v-if="course.target">
    <div class="live-list__item">
      <div class="live-item__top" @click="toCourse()">
        <span>{{course.target.course.displayedTitle}}</span>
        <i class="iconfont icon-arrow-right"></i>
      </div>
      <div class="live-item__content">
        <div class="live-content__left">
          <div
            class="live-content__subtitle"
            v-if="canShowSubtitle(course.target)"
          >课时{{course.target.number}}: {{course.target.title}}</div>
          <div class="live-content__title">
            <i :class="['iconfont',iconfont(course.target.task)]"></i>
            <span v-if="!canShowSubtitle(course.target)">课时{{course.target.number}}:</span>{{course.target.task.title}}
          </div>
          <div class="live-content__dec" v-if="canTimeShow(course.target.task)">
            <span class="live-content__time">{{ course.target | formateTime}}</span>
          </div>
        </div>
        <div class="live-content__right">
          <div class="live-btn live-btn--start" v-if="course.target.task.type !=='flash' " @click="toTask()">继续学习</div>
          <div class="live-btn live-btn--none" v-else>暂不支持</div>
        </div>
      </div>
      <div class="live-item__bottom" v-if="course.target.classroom.length" @click="toClassroom()">
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
  methods: {
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
    },
    canTimeShow(task) {
      const type = task.type;
      const showList = ["audio", "video", "live"];
      return showList.includes(type);
    },
    canShowSubtitle(target) {
      return target.task.seq !== target.seq;
    },
    toClassroom() {
      this.$emit("toClassroom",this.course.target.classroom.id)
    },
    toTask(){
      const task={
        id:this.course.target.task.id,
        type:this.course.target.task.type,
        courseId:this.course.target.course.id,
      }
      this.$emit("toTask",task)
    },
    toCourse(){
      this.$emit("toCourse",this.course.target.course.id)
    }
  }
};
</script>

<style>
</style>