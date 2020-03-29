<template>
  <van-overlay :show="show" @click="show = false" :z-index="9999">
    <div class="finish-dialog ">
      <div class="finish-dialog-content clearfix">
        <img class="finish-dialog-img" src="static/images/reportDialog.png" />
        <div class="progress-bar">
          <div class="progress-bar__content">
            <div
              :style="{ width: rate }"
              class="progress-bar__rate"
            >
               {{ rate }}
            </div>
          </div>
        </div>
        <p class="finish-dialog-text">恭喜完成</p>
        <template v-if="showNextTask">
          <p>课时：{{ nextTask.number }}{{ nextTask.title }}</p>
          <div class="finish-dialog-btn" @click="goNextTask">下一课</div>
        </template>
      </div>
    </div>
  </van-overlay>
</template>

<script>
import Api from "@/api"
import copyUrl from '@/mixins/copyUrl'
import { mapMutations } from 'vuex'
import * as types from '@/store/mutation-types'
export default {
  name: "finish-dialog",
  mixins: [copyUrl],
  data() {
    return {
      show: true
    };
  },
  props: {
    nextTask: {
      type: Object,
      default() {
        return {};
      }
    },
    completionRate: {
      type: Number,
      default: 100
    },
    courseId:{
      type: String,
      default: ''
    }
  },
  computed: {
    showNextTask() {
      if(this.nextTask===null){
        return false;
      }
      return Object.keys(this.nextTask).length;
    },
    rate(){
      return `${this.completionRate}%`
    }
  },
  methods: {
     ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE
    }),
    goNextTask() {
      const params={
        courseId:this.courseId,
        taskId:this.nextTask.id
      }
      Api.getCourseData({query: params }).then(res=>{
        this.toLearnTask(res)
      })
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
      this.showTypeDetail(task) ;
      this.show=false;
    },
    showTypeDetail(task) {
      if (task.status !== "published") {
        Toast("敬请期待");
        return;
      }
      switch (task.type) {
        case "video":
          if (task.mediaSource === "self") {
            this.setSourceType({
              sourceType: "video",
              taskId: task.id
            });
          } else {
             this.copyPcUrl(task.courseUrl);
          }
          break;
        case "audio":
          this.setSourceType({
            sourceType: "audio",
            taskId: task.id
          });
          break;
        case "text":
        case "ppt":
        case "doc":
          this.$router.push({
            name: "course_web",
            query: {
              courseId: this.courseId,
              taskId: task.id,
              type: task.type
            }
          });
          break;
        case "live":
          const nowDate = new Date();
          const endDate = new Date(task.endTime * 1000);
          const startDate = new Date(task.startTime * 1000);
          let replay = false;
          if (nowDate > endDate) {
            if (task.activity && task.activity.replayStatus == "videoGenerated") {
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
            } else if (task.activity && task.activity.replayStatus == "ungenerated") {
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
    }
  }
};
</script>
