<template>
  <van-overlay :show="show" :z-index="9999">
    <div class="finish-dialog ">
      <div class="finish-dialog-content clearfix">
        <div class="finish-dialog-top">
          <img class="finish-dialog-img" src="static/images/reportDialog.png" />
          <div class="finish-dialog-top--text">学习完成</div>
        </div>
        <div class="finish-dialog-close" @click="show = false">
          <i class="iconfont icon-guanbi"></i>
        </div>
        <div class="progress-bar">
          <div class="progress-bar__content">
            <div :style="{ width: rate }" class="progress-bar__rate">
              {{ rate }}
            </div>
          </div>
        </div>
        <p class="finish-dialog-text">恭喜完成</p>
        <template>
          <p class="text-overflow">{{ title }}</p>
          <div class="finish-dialog-btn" @click="goNextTask">下一课</div>
        </template>
      </div>
    </div>
  </van-overlay>
</template>

<script>
import Api from "@/api";
import copyUrl from "@/mixins/copyUrl";
import { mapMutations, mapState } from "vuex";
import * as types from "@/store/mutation-types";
import { Toast } from 'vant'
export default {
  name: "finish-dialog",
  mixins: [copyUrl],
  inject: ["reload"],
  data() {
    return {
      show: true,
      path: ""
    };
  },
  props: {
    finishResult: {
      type: Object,
      default() {
        return {};
      }
    },
    courseId: {
      type: String,
      default: ""
    }
  },
  computed: {
    ...mapState("course", {
      allTask: state => state.allTask,
    }),
    rate() {
      if (!this.finishResult) {
        return "0%";
      }
      return `${this.finishResult.completionRate}%`;
    },
    title() {
      if (!this.finishResult) {
        return "";
      }
      return this.allTask[this.finishResult.result.courseTaskId].title;
    }
  },
  created() {
    this.path = this.$route.path;
  },
  methods: {
    ...mapMutations("course", {
      setSourceType: types.SET_SOURCETYPE
    }),
    goNextTask() {
      if(!this.finishResult.nextTask){
        Toast('没有下一课');
        this.show = false;
        return;
      }
      const params = {
        courseId: this.courseId,
        taskId: this.finishResult.nextTask.id
      };
      Api.getCourseData({ query: params }).then(res => {
        this.toLearnTask(res);
      });
    },
    //跳转到task
    toLearnTask(task) {
      //课程再创建阶段或者和未发布状态
      if (task.status === "create") {
        Toast("课时创建中，敬请期待");
        return;
      }
      const nextTask = {
        id: task.id
      };
      // 更改store中的当前学习
      this.$store.commit(`course/${types.GET_NEXT_STUDY}`, { nextTask });
      this.showTypeDetail(task);
      this.show = false;
    },
    showTypeDetail(task) {
      if (task.status !== "published") {
        Toast("敬请期待");
        return;
      }
      switch (task.type) {
        case "video":
          this.playVedio(task);
          break;
        case "audio":
          this.playAudio(task);
          break;
        case "text":
        case "ppt":
        case "doc":
          this.$router.push({
            name: "course_web",
            query: {
              courseId: this.courseId,
              taskId: task.id,
              type: task.type,
              backUrl: `/course/${this.courseId}`
            }
          });
          this.reload();
          break;
        case "live":
          const nowDate = new Date();
          const endDate = new Date(task.endTime * 1000);
          const startDate = new Date(task.startTime * 1000);
          let replay = false;
          if (nowDate > endDate) {
            if (
              task.activity &&
              task.activity.replayStatus == "videoGenerated"
            ) {
              // 本站文件
              if (task.mediaSource === "self") {
                this.setSourceType({
                  sourceType: "video",
                  taskId: task.id
                });
              } else {
                this.copyPcUrl(task.courseUrl);
              }
              return;
            } else if (
              task.activity &&
              task.activity.replayStatus == "ungenerated"
            ) {
              Toast("暂无回放");
              return;
            } else {
              replay = true;
            }
          }

          this.$router.push({
            name: "live",
            query: {
              courseId: this.courseId,
              taskId: task.id,
              type: task.type,
              title: task.title,
              replay
            }
          });
          break;
        case "testpaper":
          const testId = task.activity.testpaperInfo.testpaperId;
          this.$router.push({
            name: "testpaperIntro",
            query: {
              testId: testId,
              targetId: task.id
            }
          });
          break;
        case "homework":
          this.$router.push({
            name: "homeworkIntro",
            query: {
              courseId: this.courseId,
              taskId: task.id
            }
          });
          break;
        case "exercise":
          this.$router.push({
            name: "exerciseIntro",
            query: {
              courseId: this.courseId,
              taskId: task.id
            }
          });
          break;
        default:
          this.copyPcUrl(task.courseUrl);
      }
    },
    playVedio(task) {
      if (task.mediaSource === "self") {
        const path = `/course/${this.courseId}`;
        if (this.$route.path === path) {
          this.setSourceType({
            sourceType: "video",
            taskId: task.id
          });
        } else {
          this.$router.push({
            path: path,
            query: {
              sourceType: "video",
              taskId: task.id
            }
          });
        }
      } else {
        this.copyPcUrl(task.courseUrl);
      }
    },
    playAudio(task) {
      const path = `/course/${this.courseId}`;
      if (this.$route.path === path) {
        this.setSourceType({
          sourceType: "audio",
          taskId: task.id
        });
      } else {
        this.$router.push({
          path: path,
          query: {
            sourceType: "audio",
            taskId: task.id
          }
        });
      }
    }
  }
};
</script>
