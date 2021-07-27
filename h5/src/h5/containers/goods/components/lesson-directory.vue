<template>
  <div>
    <div
      v-for="(lessonItem, lessonIndex) in lesson"
      :key="lessonIndex"
      class="lesson-directory"
      style="margin-left: 0; width: 100%; box-sizing: border-box;"
    >
      <div
        :id="lessonItem.tasks[lessonItem.index].id"
        :class="{ 'zb-ks': doubleLine(lessonItem.tasks[lessonItem.index]) }"
        class="lesson-title"
        @click="
          lessonCellClick(
            lessonItem.tasks[lessonItem.index],
            lessonIndex,
            lessonItem.index,
          )
        "
      >
        <div class="lesson-title-r">
          <div class="lesson-title-des">
            <!-- 非直播考试-->
            <div
              v-if="!doubleLine(lessonItem.tasks[lessonItem.index])"
              class="bl l22"
            >
              <span
                :class="{
                  lessonactive:
                    currentTask == lessonItem.tasks[lessonItem.index].id,
                }"
                class="text-overflow ks"
              >
                <span
                  v-if="isTry(lessonItem.tasks[lessonItem.index])"
                  class="tryLes"
                  >{{ $t('goods.preview') }}</span
                >
                <span
                  v-if="isFree(lessonItem.tasks[lessonItem.index])"
                  class="freeAdmission"
                  >{{ $t('goods.free') }}</span
                >
                <i
                  :class="iconfont(lessonItem.tasks[lessonItem.index])"
                  class="iconfont"
                />
                {{
                  Number(lessonItem.tasks[lessonItem.index].isOptional)
                    ? ''
                    : $t('goods.lesson')
                }}{{
                  Number(lessonItem.tasks[lessonItem.index].isOptional)
                    ? lessonItem.title
                    : `${lessonItem.tasks[lessonItem.index].number}:${
                        lessonItem.title
                      }`
                }}
              </span>
            </div>

            <!-- 直播或者考试-->
            <div
              v-if="doubleLine(lessonItem.tasks[lessonItem.index])"
              class="bl"
            >
              <div class="block-inline">
                <span
                  :class="{
                    lessonactive:
                      currentTask == lessonItem.tasks[lessonItem.index].id,
                  }"
                  class="bl text-overflow ks"
                >
                  <span
                    v-if="isTry(lessonItem.tasks[lessonItem.index])"
                    class="tryLes"
                    >{{ $t('goods.preview') }}</span
                  >
                  <span
                    v-if="isFree(lessonItem.tasks[lessonItem.index])"
                    class="freeAdmission"
                    >{{ $t('goods.free') }}</span
                  >
                  <i
                    :class="iconfont(lessonItem.tasks[lessonItem.index])"
                    class="iconfont"
                  />
                  {{
                    Number(lessonItem.tasks[lessonItem.index].isOptional)
                      ? ''
                      : $t('goods.lesson')
                  }}{{
                    Number(lessonItem.tasks[lessonItem.index].isOptional)
                      ? lessonItem.title
                      : `${lessonItem.tasks[lessonItem.index].number}:${
                          lessonItem.title
                        }`
                  }}
                </span>
                <span class="bl zbtime">
                  <span
                    :class="[liveClass(lessonItem.tasks[lessonItem.index])]"
                    >{{
                      lessonItem.tasks[lessonItem.index] | filterTaskTime
                    }}</span
                  >
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- 时长 -->
        <div class="lesson-title-l">
          <span
            class="elective-tag"
            v-if="Number(lessonItem.tasks[lessonItem.index].isOptional)"
          >
            {{ $t('goods.optional') }}
          </span>
          <span v-if="lessonItem.tasks[lessonItem.index].type != 'live'">{{
            lessonItem.tasks[lessonItem.index] | filterTaskTime
          }}</span>
          <i
            :class="studyStatus(lessonItem.tasks[lessonItem.index])"
            class="iconfont"
          />
        </div>
      </div>

      <!-- task任务 -->
      <div v-if="lessonItem.tasks.length > 1" class="lesson-items">
        <div
          v-for="(taskItem, taskIndex) in lessonItem.tasks"
          :key="taskIndex"
          :id="taskItem.id"
          @click="lessonCellClick(taskItem, lessonIndex, taskIndex)"
        >
          <div class="litem" v-if="showTask(taskItem, taskIndex)">
            <div
              :class="{ lessonactive: currentTask == Number(taskItem.id) }"
              class="litem-r text-overflow"
            >
              <span v-if="isTry(taskItem)" class="tryLes">{{ $t('goods.preview') }}</span>
              <span v-if="isFree(taskItem)" class="freeAdmission">{{ $t('goods.free') }}</span>
              <i :class="iconfont(taskItem)" class="iconfont" />
              {{ Number(taskItem.isOptional) ? '' : $t('goods.lesson')
              }}{{
                Number(taskItem.isOptional)
                  ? taskItem.title
                  : `${taskItem.number}:${taskItem.title}`
              }}
            </div>
            <div v-if="showTask(taskItem, taskIndex)" class="litem-l clearfix">
              <span :class="[liveClass(taskItem), 'text-overflow']">{{
                taskItem | filterTaskTime
              }}</span>
              <i :class="studyStatus(taskItem)" class="iconfont" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="isNoData" class="noneItem">
      <img src="static/images/none.png" class="notask" />
      <p>{{ $t('goods.thereIsNoClassYet') }}</p>
    </div>
  </div>
</template>
<script>
import redirectMixin from '@/mixins/saveRedirect';
import copyUrl from '@/mixins/copyUrl';
import { mapState, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import { Toast } from 'vant';
export default {
  name: 'LessonDirectory',
  mixins: [redirectMixin, copyUrl],
  props: {
    lesson: {
      type: Array,
      default: () => [],
    },
    errorMsg: {
      type: String,
      default: '',
    },
    taskId: {
      type: Number,
      default: -1,
    },
    taskNumber: {
      type: Number,
      default: -1,
    },
    unitNum: {
      type: Number,
      default: -1,
    },
  },
  data() {
    return {
      currentTask: '',
    };
  },
  watch: {
    taskId: {
      handler: 'getTaskId',
      immediate: true,
    },
    details(value) {
      console.log(value);
    },
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      selectedPlanId: state => state.selectedPlanId,
    }),
    hasLesson() {
      return this.lesson.length > 0;
    },
    isNoData() {
      // 在当前章下无节且无课时才显示
      return this.taskNumber === 0 && this.unitNum === 0;
    },
  },
  mounted() {
    if (Object.keys(this.$route.query).length) {
      const { sourceType, taskId } = this.$route.query;
      this.setSourceType({
        sourceType: sourceType,
        taskId: taskId,
      });
    }
  },
  methods: {
    ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE,
    }),
    isFree(task) {
      if (Number(task.isFree)) {
        return true;
      }
      return false;
    },
    isTry(task) {
      if (this.isFree(task)) {
        return false;
      }
      if (
        this.details.tryLookable &&
        Number(this.details.tryLookable) &&
        task.type === 'video' &&
        task.activity.mediaStorage
      ) {
        return true;
      }
      return false;
    },
    // 获取lesson位置
    getTaskId() {
      this.currentTask = this.taskId;
    },
    // 直播双行显示判断
    doubleLine(task) {
      if (!task.type) {
        return;
      }
      const type = task.type;
      let isDouble = false;
      if (type === 'live') {
        isDouble = true;
      } else {
        isDouble = false;
      }
      return isDouble;
    },
    showTask(taskItem, taskIndex) {
      let result = true;
      if (taskItem.mode == null) {
        if (taskIndex == 0) {
          result = false;
        }
      }
      if (taskItem.mode == 'lesson') {
        result = false;
      }
      return result;
    },
    lessonCellClick(task) {
      // 课程错误和未发布状态，不允许学习任务
      if (this.errorMsg || task.status === 'create') {
        this.$emit('showDialog');
        return;
      }

      const details = this.details;

      !details.allowAnonymousPreview &&
        this.$router.push({
          name: 'login',
          query: {
            redirect: this.redirect,
          },
        });
      if (
        !this.joinStatus &&
        (Number(task.isFree) || Number(details.tryLookable))
      ) {
        // trylook and free video click
        switch (task.type) {
          case 'video':
          case 'audio':
            this.$router.push({
              name: 'course_try',
            });

            this.setSourceType({
              sourceType: task.type,
              taskId: task.id,
            });
            break;
          case 'doc':
          case 'text':
          case 'ppt':
            this.$router.push({
              name: 'course_web',
              query: {
                courseId: this.selectedPlanId,
                taskId: task.id,
                type: task.type,
                preview: 1,
              },
            });
            break;
          default:
            return Toast(this.$t('goods.pleaseJoinTheCourses'));
        }
      } else {
        this.joinStatus ? this.showTypeDetail(task) : Toast(this.$t('goods.pleaseJoinTheCourses'));
      }
      // join after click
    },
    showTypeDetail(task) {
      if (task.status !== 'published') {
        Toast(this.$t('goods.stayTuned'));
        return;
      }

      if (task.lock) {
        Toast(this.$t('goods.needToUnlockThePreviousTask'));
        return;
      }
      switch (task.type) {
        case 'video':
          if (task.mediaSource === 'self') {
            this.setSourceType({
              sourceType: 'video',
              taskId: task.id,
            });
          } else {
            Toast(this.$t('goods.doesNotSupportThisType'));
          }
          break;
        case 'audio':
          this.setSourceType({
            sourceType: 'audio',
            taskId: task.id,
          });
          break;
        case 'text':
        case 'ppt':
        case 'doc':
          this.$router.push({
            name: 'course_web',
            query: {
              courseId: this.selectedPlanId,
              taskId: task.id,
              type: task.type,
            },
          });
          break;
        case 'live': {
          const nowDate = new Date();
          const endDate = new Date(task.endTime * 1000);
          let replay = false;
          if (nowDate > endDate) {
            if (task.activity.replayStatus === 'videoGenerated') {
              // 本站文件
              if (task.mediaSource === 'self') {
                this.setSourceType({
                  sourceType: 'video',
                  taskId: task.id,
                });
              } else {
                Toast(this.$t('goods.doesNotSupportThisType'));
              }
              return;
            } else if (task.activity.replayStatus === 'ungenerated') {
              Toast(this.$t('goods.noReplay'));
              return;
            } else {
              replay = true;
            }
          }

          this.$router.push({
            name: 'live',
            query: {
              courseId: this.selectedPlanId,
              taskId: task.id,
              type: task.type,
              title: task.title,
              replay,
            },
          });
          break;
        }
        case 'testpaper':
          // eslint-disable-next-line no-case-declarations
          const testId = task.activity.testpaperInfo.testpaperId;
          this.$router.push({
            name: 'testpaperIntro',
            query: {
              testId: testId,
              targetId: task.id,
            },
          });
          break;
        case 'homework':
          this.$router.push({
            name: 'homeworkIntro',
            query: {
              courseId: this.$route.params.id,
              taskId: task.id,
            },
          });
          break;
        case 'exercise':
          this.$router.push({
            name: 'exerciseIntro',
            query: {
              courseId: this.$route.params.id,
              taskId: task.id,
            },
          });
          break;
        default:
          Toast(this.$t('goods.doesNotSupportThisType'));
      }
    },
    // 任务图标(缺少下载)
    iconfont(task) {
      const type = task.type;
      switch (type) {
        case 'audio':
          return 'icon-yinpin';
        case 'doc':
          return 'icon-wendang';
        case 'exercise':
          return 'icon-lianxi';
        case 'flash':
          return 'icon-flash';
        case 'homework':
          return 'icon-zuoye';
        case 'live':
          return 'icon-zhibo';
        case 'ppt':
          return 'icon-ppt';
        case 'discuss':
          return 'icon-taolun';
        case 'testpaper':
          return 'icon-kaoshi';
        case 'text':
          return 'icon-tuwen';
        case 'video':
          return 'icon-shipin';
        case 'download':
          return 'icon-xiazai';
        default:
          return '';
      }
    },
    // 学习状态
    studyStatus(task) {
      if (task.lock) {
        return 'icon-suo';
      }
      if (task.result != null) {
        switch (task.result.status) {
          case 'finish':
            return 'icon-yiwanchengliang';
          case 'start':
            return 'icon-weiwancheng';
          default:
            return '';
        }
      } else {
        return 'icon-weixuexi';
      }
    },
    // 直播状态样式
    liveClass(lesson) {
      if (lesson.status !== 'published' || lesson.type !== 'live') {
        return 'nopublished';
      }
      const now = new Date().getTime();
      const endTimeStamp = new Date(lesson.endTime * 1000);
      if (now > endTimeStamp) {
        if (lesson.activity.replayStatus === 'ungenerated') {
          return 'end';
        }
        return 'back';
      }
      return 'play';
    },
  },
};
</script>
