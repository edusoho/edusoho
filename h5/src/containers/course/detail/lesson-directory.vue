<template>

  <div >
    <div v-if="hasLesson" class="lesson-directory" v-for="(lessonItem,lessonIndex) in lesson" :key="lessonIndex">
      <div
        class="lesson-title"
        :id="lessonItem.tasks[lessonItem.index].id"
        :class="{'zb-ks' : doubleLine(lessonItem.tasks[lessonItem.index])}"
        @click="lessonCellClick(lessonItem.tasks[lessonItem.index])"
      >
        <div class="lesson-title-r">
          <div class="lesson-title-des">
            <!-- 非直播考试-->
            <div class="bl l22" v-if="!doubleLine(lessonItem.tasks[lessonItem.index])">
              <!-- <span class="tryLes">试听</span> -->
              <span
                class="text-overflow ks"
                :class="{ 'lessonactive': (currentTask==lessonItem.tasks[lessonItem.index].id) }"
              >
                <i class="iconfont" :class="iconfont(lessonItem.tasks[lessonItem.index])"></i>
                {{ Number(lessonItem.tasks[lessonItem.index].isOptional) ? '选修 ' : '课时' }}{{ Number(lessonItem.tasks[lessonItem.index].isOptional) ? ' ' : `${lessonItem.tasks[lessonItem.index].number}:${lessonItem.title}`}}
              </span>
            </div>

            <!-- 直播或者考试-->
            <div class="bl" v-if="doubleLine(lessonItem.tasks[lessonItem.index])">
              <!-- <span class="tryLes">试听</span> -->
              <div class="block-inline">
                <span
                  class="bl text-overflow ks"
                  :class="{ 'lessonactive': (currentTask==lessonItem.tasks[lessonItem.index].id) }"
                >
                  <i class="iconfont" :class="iconfont(lessonItem.tasks[lessonItem.index])"></i>
                  {{ Number(lessonItem.tasks[lessonItem.index].isOptional) ? '选修 ' : '课时' }}{{ Number(lessonItem.tasks[lessonItem.index].isOptional) ? ' ' : `${lessonItem.tasks[lessonItem.index].number}:${lessonItem.title}`}}
                </span>
                <span class="bl zbtime">
                  <span
                    :class="[liveClass(lessonItem.tasks[lessonItem.index])]"
                  >{{ lessonItem.tasks[lessonItem.index]| filterTaskTime}}</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- 时长 -->
        <div class="lesson-title-l">
          <span
            v-if="lessonItem.tasks[lessonItem.index].type!='live'"
          >{{ lessonItem.tasks[lessonItem.index] | filterTaskTime }}</span>
          <i class="iconfont" :class="studyStatus(lessonItem.tasks[lessonItem.index])"></i>
        </div>
      </div>

      <!-- task任务 -->
      <div class="lesson-items" v-if="lessonItem.tasks.length>1">
        <div
          class="litem"
          v-for="(taskItem,taskIndex) in lessonItem.tasks"
          :id="taskItem.id"
          :key="taskIndex"
          v-if="showTask(taskItem,taskIndex)"
          @click="lessonCellClick(taskItem)"
        >
          <div
            class="litem-r text-overflow"
            :class="{ 'lessonactive': (currentTask==Number(taskItem.id)) }"
          >
            <!-- <span class="tryLes">试听</span> -->
            <i class="iconfont" :class="iconfont(taskItem)"></i>
            {{ Number(taskItem.isOptional) ? '选修 ' : '课时' }}{{ Number(taskItem.isOptional) ? ' ' : `${taskItem.number}:${taskItem.title}`}}
          </div>
          <div class="litem-l clearfix">
            <span :class="[liveClass(taskItem),'text-overflow']">{{ taskItem | filterTaskTime }}</span>
            <i class="iconfont" :class="studyStatus(taskItem)"></i>
          </div>
        </div>
      </div>
    </div>
    <div v-if="taskNumber==0" class="noneItem">
      <img src="static/images/none.png" class="notask" />
      <p>暂时还没有课时哦...</p>
    </div>
  </div>
  
</template>
<script>
import redirectMixin from "@/mixins/saveRedirect";
import { mapState, mapMutations } from "vuex";
import * as types from "@/store/mutation-types";
import { Dialog, Toast } from "vant";
export default {
  name: "lessonDirectory",
  mixins: [redirectMixin],
  props: {
    lesson: {
      type: Array,
      default: () => []
    },
    errorMsg: {
      type: String,
      default: ""
    },
    taskId: {
      type: Number,
      default: -1
    },
    taskNumber: {
      type: Number,
      default: -1
    }
  },
  data() {
    return {
      currentTask: ""
    };
  },
  watch: {
    taskId: {
      handler: "getTaskId",
      immediate: true
    }
  },
  computed: {
    ...mapState("course", {
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      selectedPlanId: state => state.selectedPlanId
    }),
    hasLesson(){
      if(this.lesson.length>0){
        return true
      }else {
        return false
      }
    }
  },
  methods: {
    ...mapMutations("course", {
      setSourceType: types.SET_SOURCETYPE
    }),
    //获取lesson位置
    getTaskId() {
      this.currentTask = this.taskId;
    },
    //直播双行显示判断
    doubleLine(task) {
      if(!task.type){
        return
      }
      let type=task.type;
      let isDouble=false;
      if (type === "live") {
        isDouble = true;
      } else {
        isDouble = false;
      }
      return isDouble;
    },
    showTask(taskItem,taskIndex){
      let result=true
      if(taskItem.mode==null ){
        if(taskIndex==0){
          result=false
        }
      }
      if(taskItem.mode=='lesson'){
        result=false
      }
      return result

    },
    lessonCellClick(task) {
      // 课程错误和未发布状态，不允许学习任务
      if (this.errorMsg) {
        this.$emit("showDialog");
        return;
      }
      if(task.status === "create"){
        Toast("敬请期待");
        return;
      }

      this.currentTask = task.id;
      const details = this.details;

      !details.allowAnonymousPreview &&
        this.$router.push({
          name: "login",
          query: {
            redirect: this.redirect
          }
        });
      this.joinStatus ? this.showTypeDetail(task) : "";
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
            Toast("暂不支持此类型");
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
              courseId: this.selectedPlanId,
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
            if (task.activity.replayStatus == "videoGenerated") {
              // 本站文件
              if (task.mediaSource === "self") {
                this.setSourceType({
                  sourceType: "video",
                  taskId: task.id
                });
              } else {
                Toast("暂不支持此类型");
              }
              return;
            } else if (task.activity.replayStatus == "ungenerated") {
              Toast("暂无回放");
              return;
            } else {
              replay = true;
            }
          }

          this.$router.push({
            name: "live",
            query: {
              courseId: this.selectedPlanId,
              taskId: task.id,
              type: task.type,
              title: task.title,
              replay
            }
          });
          break;
        case 'testpaper':
          const testId = task.activity.testpaperInfo.testpaperId;
          this.$router.push({
            name: 'testpaperIntro',
            params: {
              testId: testId
            },
            query: {
              targetId: task.id,
            }
          })
          break;
        default:
          Toast("暂不支持此类型");
      }
    },
    //任务图标(缺少下载)
    iconfont(task) {
      let type=task.type;
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
    //学习状态
    studyStatus(task) {
      if (task.lock) {
        return "icon-suo";
      }
      if (task.result != null) {
        switch (task.result.status) {
          case "finish":
            return "icon-yiwanchengliang";
            break;
          case "start":
            return "icon-weiwancheng";
            break;
          default:
            return "";
        }
      } else {
        return "icon-weixuexi";
      }
    },
    //直播状态样式
    liveClass(lesson) {
      if (lesson.status!='published'||lesson.type != "live") {
        return "nopublished";
      }
      const now = new Date().getTime();
      const startTimeStamp = new Date(lesson.startTime * 1000);
      const endTimeStamp = new Date(lesson.endTime * 1000);
      if (now > endTimeStamp) {
        if (lesson.activity.replayStatus === "ungenerated") {
          return "end";
        }
        return "back";
      }
      return "play";
    }
  }
};
</script>