<template>
  <div>
    <template v-for="(lessonItem, lessonIndex) in lesson">
      <div v-if="hasLesson" :key="lessonIndex" class="lesson-directory">
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
                <!-- <span class="tryLes">试听</span> -->
                <span
                  :class="{
                    lessonactive:
                      currentTask == lessonItem.tasks[lessonItem.index].id,
                  }"
                  class="ks"
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
                <i :class="iconfont(taskItem)" class="iconfont" />
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
                <i :class="studyStatus(taskItem)" class="iconfont" />
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>
    <div v-if="isNoData" class="noneItem">
      <img src="static/images/none.png" class="notask" />
      <p>{{ $t('courseLearning.thereIsNoClassYet') }}</p>
    </div>
  </div>
</template>
<script>
import redirectMixin from '@/mixins/saveRedirect';
import copyUrl from '@/mixins/copyUrl';
import { mapState, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import { Toast, Dialog } from 'vant';
import Api from '@/api';
import { closedToast } from '@/utils/on-status.js';


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
      default: () => {},
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
      }
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
    // 获取lesson位置
    getTaskId() {
      this.currentTask = this.taskId;
    },
    // 直播双行显示判断
    doubleLine(task) {
      if (task.isReplay) {
        return;
      }

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
    // 判断课程关闭后是否可以学习
    isCanLearn(task) {
      const allowedTaskTypes = ['testpaper', 'homework', 'exercise'];
      const isTaskTypeAllowed = allowedTaskTypes.includes(task.type);
      const isTaskResultIncomplete = !task.result || task.result.status != 'finish';

      if(this.getAgainCourse?.courseSet?.status !== 'closed') {
        return true
      }

      if(!isTaskTypeAllowed) {
        return false
      }

      if(isTaskTypeAllowed && isTaskResultIncomplete) {
        return false
      }

      return true
    },
    async getCourse() {
      const query = { courseId: this.selectedPlanId };
      await Api.getCourseDetail({ query }).then((res) => {
        this.getAgainCourse = res;
      })
    },
    // 判断手机类型
    judgePhoneType() {
        let isAndroid = false, isIOS = false, isIOS9 = false, version,
            u = navigator.userAgent,
            ua = u.toLowerCase();
        //Android系统
        if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {   //android终端或者uc浏览器
            isAndroid = true
        }
        //ios
        if (ua.indexOf("like mac os x") > 0) {
            let regStr_saf = /os [\d._]*/gi;
            let verinfo = ua.match(regStr_saf);
            version = (verinfo + "").replace(/[^0-9|_.]/ig, "").replace(/_/ig, ".");
        }
        let version_str = version + "";
        // ios9以上
        if (version_str !== "undefined" && version_str.length > 0) {
            version = parseInt(version);
            if (version >= 8) {
                isIOS9 = true
            } else {
                isIOS = true
            }
        }
        return {isAndroid, isIOS, isIOS9};
    },
    // 判断是否在微信中
    isWeiXin() {
        return /micromessenger/i.test(navigator.userAgent.toLowerCase()) || typeof navigator.wxuserAgent !== 'undefined'
    },
    goConfirmAddr() {
        let { isAndroid } = this.judgePhoneType();
        window.location.href =  !isAndroid  ? this.CONFIG.ios : this.CONFIG.android ;
    },
    openApp(url, callback={}) {
        let {isAndroid, isIOS, isIOS9} = this.judgePhoneType();
        console.log(isAndroid, isIOS, isIOS9);
        if(this.isWeiXin()){
            alert("请您在浏览器中打开,即可下载") ;
            return ;
        }

        if (isAndroid || isIOS) {
            let hasApp = true, t = 1000,
                t1 = Date.now(),
                ifr = document.createElement("iframe");
            setTimeout(function () {
                if (!hasApp) {
                    callback && callback()
                }
                document.body.removeChild(ifr);
            }, 2000);

            ifr.setAttribute('src', url);
            ifr.setAttribute('style', 'display:none');
            document.body.appendChild(ifr);

            setTimeout(function () { //启动app时间较长处理
                let t2 = Date.now();
                if (t2 - t1 < t + 100) {
                    hasApp = false;
                }
            }, t);
        }
        if (isIOS9) {
            //  window.location.href = url;
            setTimeout(function () {
                callback && callback()
            }, 250);
            setTimeout(function () {
            //  window.location.reload();
            }, 1000);
        }
    },
    async lessonCellClick(task, lessonIndex, taskIndex) {
      const onlyAppType = ['video', 'audio', 'live']
      await this.getCourse()

      if(!this.isCanLearn(task)) {
        return closedToast('course');
      }

      if(Number(this.isOnlyApp) && onlyAppType.includes(task.type)) {
        Dialog.confirm({
          message: '将为您跳转至App进行学习',
          confirmButtonText: '继续',
          className: 'only-app-dialog'
        }).then(() => {
          this.openApp('com.edusoho.zhixiang://', this.goConfirmAddr())
        }).catch(() => {
          // on cancel
        });
        return;
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
      this.$store.commit(`course/${types.GET_NEXT_STUDY}`, { nextTask });

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
					const { testpaperId: testId, answerRecordId: resultId } = task.activity.testpaperInfo;
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
              const { endTime } = res.task.activity.testpaperInfo
							const result = res.testpaperResult
              if ((result?.status == 'finished' || result?.status == 'reviewing') && (endTime * 1000 > Date.now() || endTime == 0 || endTime == null) ) {
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
              Toast.fail(err.message);
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
      const { type, isReplay } = task;

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
      // if (lesson.status != 'published' || lesson.type != 'live') {
      //   return 'nopublished';
      // }
      // const now = new Date().getTime();
      // const endTimeStamp = new Date(lesson.endTime * 1000);
      // if (now > endTimeStamp) {
      //   if (lesson.activity.replayStatus === 'ungenerated') {
      //     return 'end';
      //   }
      //   return 'back';
      // }
      // return 'play';
    },
  },
};
</script>
<style scoped>
</style>
