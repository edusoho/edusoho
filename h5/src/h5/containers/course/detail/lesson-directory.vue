<template>
  <div>
    <template v-for="(lessonItem, lessonIndex) in lesson">
      <div v-if="hasLesson" :key="lessonIndex" class="lesson-directory">
        <div
          :id="lessonItem.tasks[lessonItem.index].id"
          :class="{ 'zb-ks': doubleLine(lessonItem.tasks[lessonItem.index]), 'py-16': showPaddingY(lessonItem.tasks[lessonItem.index].videoMaxLevel) }"
          class="lesson-title flex justify-between items-center relative px-12"
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
                <!-- <span class="tryLes">试听</span> -->
                <span
                  :class="{
                    lessonactive:
                      currentTask == lessonItem.tasks[lessonItem.index].id,
                  }"
                  class="ks"
                  style="align-items: center;"
                >
                  <span v-if="lessonItem.tasks[lessonItem.index].videoMaxLevel === '2k'"
                        class="px-8 text-white text-12 font-medium bg-black bg-opacity-80 rounded-bl-8 mr-8"
                        style="padding-top: 3px; padding-bottom: 3px; line-height: 12px; border-bottom-left-radius: 8px; border-top-right-radius: 8px;">2K 优享</span>
                  <span v-if="lessonItem.tasks[lessonItem.index].videoMaxLevel === '4k'"
                        class="px-8 text-[#492F0B] text-12 font-medium bg-gradient-to-l from-[#F7D27B] to-[#FCEABE] mr-8"
                        style="padding-top: 3px; padding-bottom: 3px; line-height: 12px; border-bottom-left-radius: 8px; border-top-right-radius: 8px;">4K 臻享</span>
                  <i
                    :class="iconfont(lessonItem.tasks[lessonItem.index])"
                    class="iconfont"
                  />
                  <div class="flex flex-col">
                    <div>
                      {{
                        Number(lessonItem.tasks[lessonItem.index].isOptional)
                          ? ''
                          : $t('courseLearning.lesson')
                      }}{{
                        Number(lessonItem.tasks[lessonItem.index].isOptional)
                          ? lessonItem.title
                          : `${lessonItem.tasks[lessonItem.index].number}:${
                            lessonItem.title
                          }`
                      }}
                    </div>
                    <br/>
                    <div v-if="lessonItem.tasks[lessonItem.index].isLastLearn" class="last-learn-task">上次学到</div>
                  </div>
                </span>
              </div>

              <!-- 直播或者考试-->
              <div
                v-if="doubleLine(lessonItem.tasks[lessonItem.index])"
                class="bl"
              >
                <!-- <span class="tryLes">试听</span> -->
                <div class="block-inline">
                  <span
                    :class="{
                      lessonactive:
                        currentTask == lessonItem.tasks[lessonItem.index].id,
                    }"
                    class="bl ks"
                    style="display: flex;"
                  >
                    <i
                      :class="iconfont(lessonItem.tasks[lessonItem.index])"
                      class="iconfont"
                    />
                    <div>
                      {{
                        Number(lessonItem.tasks[lessonItem.index].isOptional)
                          ? ''
                          : $t('courseLearning.lesson')
                      }}{{
                        Number(lessonItem.tasks[lessonItem.index].isOptional)
                          ? lessonItem.title
                          : `${lessonItem.tasks[lessonItem.index].number}:${
                            lessonItem.title
                          }`
                      }}
                    </div>
                  </span>
                  <span class="bl zbtime">
                    <span
                      :class="[liveClass(lessonItem.tasks[lessonItem.index])]"
                    >{{
                        lessonItem.tasks[lessonItem.index] | filterTaskTime
                      }}</span
                    >
                  </span>
                  <div v-if="lessonItem.tasks[lessonItem.index].isLastLearn" class="last-learn-task">上次学到</div>
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
              {{ $t('courseLearning.optional') }}
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
          <template v-for="(taskItem, taskIndex) in lessonItem.tasks">
            <div
              v-if="showTask(taskItem, taskIndex)"
              :id="taskItem.id"
              :key="taskIndex"
              class="litem"
              @click="lessonCellClick(taskItem, lessonIndex, taskIndex)"
            >
              <div
                :class="{ lessonactive: currentTask == Number(taskItem.id) }"
                class="litem-r"
              >
                <!-- <span class="tryLes">试听</span> -->
                <i :class="iconfont(taskItem)" class="iconfont"/>
                <div>
                  {{
                    Number(taskItem.isOptional)
                      ? ''
                      : $t('courseLearning.lesson')
                  }}{{
                    Number(taskItem.isOptional)
                      ? taskItem.title
                      : `${taskItem.number}:${taskItem.title}`
                  }}
                </div>
              </div>
              <div class="litem-l clearfix">
                <span :class="[liveClass(taskItem)]">{{
                    taskItem | filterTaskTime
                  }}</span>
                <i :class="studyStatus(taskItem)" class="iconfont"/>
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>
    <div v-if="isNoData" class="noneItem">
      <img src="static/images/none.png" class="notask"/>
      <p>{{ $t('courseLearning.thereIsNoClassYet') }}</p>
    </div>
  </div>
</template>
<script>
import redirectMixin from '@/mixins/saveRedirect';
import copyUrl from '@/mixins/copyUrl';
import {mapState, mapMutations} from 'vuex';
import * as types from '@/store/mutation-types';
import {Toast, Dialog} from 'vant';
import Api from '@/api';
import {closedToast} from '@/utils/on-status.js';
import {SET_TASK_SATUS} from '@/store/mutation-types';


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
    courseSet: {
      type: Object,
      default: () => {
      },
    }
  },
  data() {
    return {
      currentTask: '',
      getAgainCourse: {},
      CONFIG: {
        android: 'https://a.app.qq.com/o/simple.jsp?pkgname=com.edusoho.zhixiang',
        ios: 'https://apps.apple.com/cn/app/知享学堂/id887301045',
        scheme: 'com.qdxxzy.user://'
      },
    };
  },
  watch: {
    taskId: {
      handler: 'getTaskId',
      immediate: true,
    },
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      selectedPlanId: state => state.selectedPlanId,
    }),
    ...mapState({
      isOnlyApp: state => state.courseSettings?.only_learning_on_APP
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
      const {sourceType, taskId} = this.$route.query;
      this.setSourceType({
        sourceType: sourceType,
        taskId: taskId,
      });
    }

    this.initLastLearnTaskEvent();
  },
  methods: {
    ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE,
      setTaskStatus: types.SET_TASK_SATUS
    }),
    initLastLearnTaskEvent() {
      if (this.$route.query.lastLearnTaskId && this.$route.query.lastLearnTaskType) {
        const {lastLearnTaskType, lastLearnTaskId} = this.$route.query;
        this.setSourceType({
          sourceType: lastLearnTaskType,
          taskId: lastLearnTaskId,
        });
        const element = document.getElementById(this.$route.query.lastLearnTaskId);
        if (element) {
          element.click();
        }
      }
    },
    // 获取lesson位置
    getTaskId() {
      this.currentTask = this.taskId;
    },
    // 直播双行显示判断
    doubleLine(task) {
      if (task.isReplay) return;
      if (!task.type) return;

      const type = task.type;
      let isDouble = false;
      isDouble = type === 'live';
      return isDouble;
    },
    showPaddingY(videoMaxLevel) {
      return videoMaxLevel === '2k' || videoMaxLevel === '4k';
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
    // 判断课程关闭后是否可以学习
    isCanLearn(task) {
      const allowedTaskTypes = ['testpaper', 'homework', 'exercise'];
      const isTaskTypeAllowed = allowedTaskTypes.includes(task.type);
      const isTaskResultIncomplete = !task.result || task.result.status != 'finish';

      if (this.getAgainCourse?.courseSet?.status !== 'closed') {
        return true;
      }

      if (!isTaskTypeAllowed) {
        return false;
      }

      if (isTaskTypeAllowed && isTaskResultIncomplete) {
        return false;
      }

      return true;
    },
    async getCourse() {
      const query = {courseId: this.selectedPlanId};
      await Api.getCourseDetail({query}).then((res) => {
        this.getAgainCourse = res;
      });
    },
    async getData(id) {
      let data = {};
      try {
        await Api.getMedia({
          query: {
            courseId: this.selectedPlanId,
            taskId: id,
          },
          params: {
            version: 'escloud',
          },
        }).then((res) => {
          if (res.media) {
            data = res.media;
          }
        });
      } catch (error) {
        console.log(error);
      }

      return data;
    },
    detectBrowserInfo() {
      const userAgent = navigator.userAgent.toLowerCase();

      // 判断是否为微信浏览器
      const isWechat = /micromessenger/i.test(userAgent);
      // 判断是否为企业微信（即微信工作版）
      const isWechatWork = /wxwork/i.test(userAgent);

      // 判断是否为钉钉内置浏览器
      const isDingTalk = /dingtalk/i.test(userAgent);

      // 飞书内置浏览器可能包含 "lark" 关键字，但请核实最新版本 UA 以确保准确性
      const isFeishu = /lark/i.test(userAgent);

      if (isWechat || isWechatWork || isDingTalk || isFeishu) {
        return true;
      }

      return false;
    },
    async lessonCellClick(task, lessonIndex, taskIndex) {
      await this.getCourse();
      if (!this.isCanLearn(task)) {
        return closedToast('course');
      }

      if (task.type === 'live' || task.type === 'video') {
        const media = await this.getData(task.id);

        if (media.isEncryptionPlus && media.securityVideoPlayer) {
          Toast('请在APP学习');
          return;
        }

        if (media.isEncryptionPlus && !media.securityVideoPlayer && !this.detectBrowserInfo()) {
          Toast('请在APP学习或使用钉钉/飞书/微信/企业微信内置浏览器打开');
          return;
        }
      }

      this.$store.commit(types.SET_TASK_SATUS, '');

      // 课程错误和未发布状态，不允许学习任务
      if (this.errorMsg && !Number(task.isFree)) {
        this.$emit('showDialog');
        return;
      }

      if (task.lock) {
        Toast(this.$t('courseLearning.needToUnlockThePreviousTask'));
        return;
      }
      // 课程再创建阶段或者和未发布状态
      if (task.status === 'create' || task.status !== 'published') {
        Toast(this.$t('courseLearning.stayTuned'));
        return;
      }
      const nextTask = {
        id: task.id,
      };

      // 更改store中的当前学习
      this.$store.commit(`course/${types.GET_NEXT_STUDY}`, {nextTask});

      const details = this.details;
      !details.allowAnonymousPreview &&
      this.$router.push({
        name: 'login',
        query: {
          redirect: this.redirect,
        },
      });

      if (this.joinStatus) {
        this.showTypeDetail(task);
      }

    },
    showTypeDetail(task) {
      if (task.status !== 'published') {
        Toast(this.$t('courseLearning.stayTuned'));
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
          Toast(this.$t('courseLearning.doesNotSupportThisType'));
        }
        break;
      case 'audio':
        this.setSourceType({
          sourceType: 'audio',
          taskId: task.id,
        });
        break;
      case 'ppt':
        this.setSourceType({
          sourceType: 'ppt',
          taskId: task.id,
        });
        break;
      case 'text':
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
        if (nowDate > endDate && task.liveStatus === 'closed') {
          if (task.activity.replayStatus == 'videoGenerated') {
            // 本站文件
            this.setSourceType({
              sourceType: 'video',
              taskId: task.id,
            });
            return;
          } else if (task.activity.replayStatus == 'ungenerated') {
            Toast(this.$t('courseLearning.noReplay'));
            return;
          } else {
            replay = true;
            this.setSourceType({
              sourceType: 'video',
              taskId: task.id,
            });
            this.setTaskStatus('finish');
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
      case 'testpaper': {
        const {testpaperId: testId, answerRecordId: resultId} = task.activity.testpaperInfo;
        Api.testpaperIntro({
          params: {
            targetId: task.id,
            targetType: 'task',
          },
          query: {
            testId: testId,
          },
        })
          .then(res => {
            const {endTime} = res.task.activity.testpaperInfo;
            const result = res.testpaperResult;
            if ((result?.status == 'finished' || result?.status == 'reviewing') && (endTime * 1000 > Date.now() || endTime == 0 || endTime == null)) {
              this.$router.push({
                name: 'testpaperResult',
                query: {
                  testId,
                  targetId: task.id,
                  resultId,
                  courseId: this.$route.params.id
                },
              });
            } else {
              this.$router.push({
                name: 'testpaperIntro',
                query: {
                  testId,
                  targetId: task.id,
                  courseId: this.$route.params.id
                },
              });
            }
          })
          .catch(err => {
            if (err.code === 4032203) {
              Dialog.alert({
                title: '试卷已关闭',
                confirmButtonText: '确定',
                confirmButtonColor: '#00BE63',
              });
            } else {
              Toast.fail(err.message);
            }
          });

        break;
      }
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
        // 防止视频遮挡了弹出框
        this.setSourceType({
          sourceType: 'img',
          taskId: task.id,
        });
        this.copyPcUrl(task.courseUrl);
      }
    },
    // 任务图标(缺少下载)
    iconfont(task) {
      const {type, isReplay} = task;

      if (isReplay) return 'icon-replay';

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
      if (lesson.status != 'published' || lesson.type != 'live') {
        return 'nopublished';
      }

      if (lesson.activity.replayStatus === 'generated') {
        return 'back';
      }

      if (lesson.progressStatus === 'closed') {
        return 'end';
      }

      if (lesson.progressStatus === 'created') {
        return 'play';
      }

      if (lesson.progressStatus === 'live') {
        return 'play';
      }
    },
  },
};
</script>
<style scoped>
</style>
